<?php

namespace SmartDato\FedEx;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use SmartDato\FedEx\Auth\OAuthClient;
use SmartDato\FedEx\Enums\LabelResponseOptionEnum;
use SmartDato\FedEx\Enums\TrackBy;
use SmartDato\FedEx\Payloads\ShipmentPayload;

class Fedex
{
    protected string $baseUrl;

    protected string $accountNumber;

    protected LabelResponseOptionEnum $labelResponseOptions;

    protected string $contentType = 'application/json';

    public function __construct(
        protected OAuthClient $oauthClient,
        protected array $config = []
    ) {
        $environment = $this->getConfigValue('environment', 'sandbox');
        $baseUrls = $this->getConfigValue('base_url', []);
        $this->baseUrl = $baseUrls[$environment] ?? $baseUrls['sandbox'] ?? 'https://apis-sandbox.fedex.com';

        $this->accountNumber = $this->getConfigValue('account_number');
        $this->labelResponseOptions = $this->getLabelResponseOptions();
    }

    protected function getConfigValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? config("fedex.{$key}", $default);
    }

    protected function getLabelResponseOptions(): LabelResponseOptionEnum
    {
        $value = $this->getConfigValue('label_response_options', 'URL_ONLY');

        return LabelResponseOptionEnum::tryFrom($value) ?? LabelResponseOptionEnum::URL_ONLY;
    }

    /**
     * @throws ConnectionException
     */
    public function createShipment(ShipmentPayload $payload): JsonResponse
    {
        try {
            $content = json_encode($this->buildPayload($payload), JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to encode payload to JSON: '.$e->getMessage());
        }

        $response = Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'content-type' => $this->contentType,
                'authorization' => $this->oauthClient->getAuthorizationHeader(),
            ])
            ->withBody($content)
            ->post('/ship/v1/shipments');

        return $response->json();
    }

    /**
     * Track a shipment by tracking number, TCN, or reference number
     *
     * @param  string  $trackingValue  The tracking number, TCN, or reference number
     * @param  TrackBy  $trackBy  The type of tracking
     * @param  array  $options  Additional tracking options (includeDetailedScans, shipDateBegin, shipDateEnd)
     *
     * @throws ConnectionException
     */
    public function trackShipment(
        string $trackingValue,
        TrackBy $trackBy = TrackBy::TRACKING_NUMBER,
        array $options = []
    ) {
        try {
            $payload = $trackBy->build($trackingValue, $options);
            $content = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to encode payload to JSON: '.$e->getMessage());
        }

        $response = Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'content-type' => $this->contentType,
                'authorization' => $this->oauthClient->getAuthorizationHeader(),
            ])
            ->withBody($content)
            ->post('/track/v1/'.$trackBy->value);

        return $response;
    }

    /**
     * Track multiple shipments at once
     *
     * @param  array  $trackingNumbers  Array of tracking numbers
     * @param  array  $options  Additional tracking options
     *
     * @throws ConnectionException
     */
    public function trackMultipleShipments(array $trackingNumbers, array $options = []): JsonResponse
    {
        try {
            $trackingInfo = array_map(fn ($number) => [
                'trackingNumberInfo' => [
                    'trackingNumber' => $number,
                ],
            ], $trackingNumbers);

            $payload = [
                'trackingInfo' => $trackingInfo,
                'includeDetailedScans' => $options['includeDetailedScans'] ?? false,
            ];

            $content = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to encode payload to JSON: '.$e->getMessage());
        }

        $response = Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'content-type' => $this->contentType,
                'authorization' => $this->oauthClient->getAuthorizationHeader(),
            ])
            ->withBody($content)
            ->post('/track/v1/trackingnumbers');

        return $response->json();
    }

    /**
     * Get the OAuth client instance
     */
    public function getOAuthClient(): OAuthClient
    {
        return $this->oauthClient;
    }

    /**
     * Refresh the OAuth token
     *
     * @throws ConnectionException
     */
    public function refreshToken(): string
    {
        return $this->oauthClient->refreshToken();
    }

    /**
     * Set the account number (override config)
     */
    public function setAccountNumber(string $accountNumber): self
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * Set the label response options (override config)
     */
    public function setLabelResponseOptions(LabelResponseOptionEnum $options): self
    {
        $this->labelResponseOptions = $options;

        return $this;
    }

    /**
     * Get the current account number
     */
    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    /**
     * Get the base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    private function buildPayload(ShipmentPayload $payload): array
    {
        return [
            'requestedShipment' => $payload->build(),
            'labelResponseOptions' => $this->labelResponseOptions->value,
            'accountNumber' => [
                'value' => $this->accountNumber,
            ],
        ];
    }
}
