<?php

namespace SmartDato\FedEx\TradeDocuments;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use SmartDato\FedEx\Auth\OAuthClient;
use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Payloads\EtdMultiUploadPayload;
use SmartDato\FedEx\Payloads\EtdUploadDocumentPayload;
use SmartDato\FedEx\Payloads\LhsImageUploadPayload;

/**
 * Upload methods return the raw HTTP client Response so consumers can both
 * decode it (->json()) and persist the untouched body (->body()) for logging.
 */
class TradeDocumentsClient
{
    public function __construct(
        protected OAuthClient $oauthClient,
        protected string $baseUrl,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function upload(
        EtdUploadDocumentPayload $payload,
        string $filePath,
        ?string $customerTransactionId = null,
    ): Response {
        return $this->postSingleDocument(
            '/documents/v1/etds/upload',
            $payload,
            $filePath,
            $customerTransactionId,
            $payload->getFileName(),
        );
    }

    /**
     * @throws ConnectionException
     */
    public function uploadMultiple(
        EtdMultiUploadPayload $payload,
        ?string $customerTransactionId = null,
    ): Response {
        $request = $this->baseRequest($customerTransactionId)
            ->attach('documentInformation', $this->encodeJson($payload->build()), 'documentInformation.json', [
                'Content-Type' => 'application/json',
            ]);

        foreach ($payload->getMetaData() as $meta) {
            $request->attach('fileAttachments', $this->openFile($meta->getFilePath()), $meta->getFileName());
        }

        return $request->post('/documents/v1/etds/multiupload');
    }

    /**
     * @throws ConnectionException
     */
    public function uploadLetterheadOrSignature(
        LhsImageUploadPayload $payload,
        string $filePath,
        ?string $customerTransactionId = null,
    ): Response {
        return $this->postSingleDocument(
            '/documents/v1/lhsimages/upload',
            $payload,
            $filePath,
            $customerTransactionId,
            $payload->getFileName(),
        );
    }

    /**
     * FedEx validates that the attached file's name matches the name declared in
     * the document JSON, so the attachment must not fall back to the storage
     * basename (often a hashed file name).
     *
     * @throws ConnectionException
     */
    protected function postSingleDocument(
        string $endpoint,
        PayloadContract $payload,
        string $filePath,
        ?string $customerTransactionId,
        ?string $fileName = null,
    ): Response {
        return $this->baseRequest($customerTransactionId)
            ->attach('document', $this->encodeJson($payload->build()), 'document.json', [
                'Content-Type' => 'application/json',
            ])
            ->attach('attachment', $this->openFile($filePath), $fileName ?? basename($filePath))
            ->post($endpoint);
    }

    /**
     * @throws ConnectionException
     */
    protected function baseRequest(?string $customerTransactionId): PendingRequest
    {
        $headers = [
            'authorization' => $this->oauthClient->getAuthorizationHeader(),
        ];

        if (! empty($customerTransactionId)) {
            $headers['x-customer-transaction-id'] = $customerTransactionId;
        }

        return Http::baseUrl($this->baseUrl)->withHeaders($headers)->asMultipart();
    }

    /**
     * @return resource
     */
    protected function openFile(string $filePath)
    {
        $handle = @fopen($filePath, 'rb');

        if ($handle === false) {
            throw new InvalidArgumentException("File not found or not readable: {$filePath}");
        }

        return $handle;
    }

    protected function encodeJson(array $payload): string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to encode payload to JSON: '.$e->getMessage(), 0, $e);
        }
    }
}
