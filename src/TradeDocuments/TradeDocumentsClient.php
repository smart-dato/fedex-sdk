<?php

namespace SmartDato\FedEx\TradeDocuments;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use SmartDato\FedEx\Auth\OAuthClient;
use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Payloads\EtdMultiUploadPayload;
use SmartDato\FedEx\Payloads\EtdUploadDocumentPayload;
use SmartDato\FedEx\Payloads\LhsImageUploadPayload;
use SmartDato\FedEx\Support\ApiCallRecord;

class TradeDocumentsClient
{
    /** @var (callable(ApiCallRecord): void)|null */
    protected $recorder = null;

    public function __construct(
        protected OAuthClient $oauthClient,
        protected string $baseUrl,
    ) {}

    /**
     * Register a callback that receives an ApiCallRecord for every request
     * sent to the FedEx document API, so the consuming application can
     * persist the raw request and response. The callback also fires when the
     * request fails to connect; the record then carries a null response.
     *
     * @param  (callable(ApiCallRecord): void)|null  $recorder
     */
    public function recordUsing(?callable $recorder): self
    {
        $this->recorder = $recorder;

        return $this;
    }

    /**
     * @throws ConnectionException
     */
    public function upload(
        EtdUploadDocumentPayload $payload,
        string $filePath,
        ?string $customerTransactionId = null,
    ): array {
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
    ): array {
        $documentInformation = $payload->build();

        $request = $this->baseRequest($customerTransactionId)
            ->attach('documentInformation', $this->encodeJson($documentInformation), 'documentInformation.json', [
                'Content-Type' => 'application/json',
            ]);

        $fileNames = [];
        foreach ($payload->getMetaData() as $meta) {
            $fileNames[] = $meta->getFileName();
            $request->attach('fileAttachments', $this->openFile($meta->getFilePath()), $meta->getFileName());
        }

        return $this->postAndRecord($request, '/documents/v1/etds/multiupload', $this->encodeJson([
            'documentInformation' => $documentInformation,
            'fileAttachments' => $fileNames,
        ]));
    }

    /**
     * @throws ConnectionException
     */
    public function uploadLetterheadOrSignature(
        LhsImageUploadPayload $payload,
        string $filePath,
        ?string $customerTransactionId = null,
    ): array {
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
    ): array {
        $document = $payload->build();
        $fileName ??= basename($filePath);

        $request = $this->baseRequest($customerTransactionId)
            ->attach('document', $this->encodeJson($document), 'document.json', [
                'Content-Type' => 'application/json',
            ])
            ->attach('attachment', $this->openFile($filePath), $fileName);

        return $this->postAndRecord($request, $endpoint, $this->encodeJson([
            'document' => $document,
            'attachment' => $fileName,
        ]));
    }

    /**
     * @throws ConnectionException
     */
    protected function postAndRecord(PendingRequest $request, string $endpoint, string $requestDescription): array
    {
        $response = null;

        try {
            $response = $request->post($endpoint);

            return $response->json() ?? [];
        } finally {
            if ($this->recorder !== null) {
                ($this->recorder)(new ApiCallRecord(
                    method: 'POST',
                    url: rtrim($this->baseUrl, '/').$endpoint,
                    request: $requestDescription,
                    status: $response?->status(),
                    response: $response?->body(),
                ));
            }
        }
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
