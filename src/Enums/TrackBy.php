<?php

namespace SmartDato\FedEx\Enums;

enum TrackBy: string
{
    // Track by Tracking Number
    // POST /track/v1/trackingnumbers
    case TRACKING_NUMBER = 'trackingnumbers';

    // Track by Tracking Control Number
    // POST /track/v1/tcn
    case TCN = 'tcn';

    // Track by References
    // POST /track/v1/referencenumbers
    case REFERENCE_NUMBER = 'referencenumbers';

    /**
     * Build the tracking request payload
     */
    public function build(string $value, array $additionalParams = []): array
    {
        $payload = match ($this) {
            self::TRACKING_NUMBER => [
                'trackingInfo' => [
                    [
                        'trackingNumberInfo' => [
                            'trackingNumber' => $value,
                        ],
                    ],
                ],
                'includeDetailedScans' => $additionalParams['includeDetailedScans'] ?? false,
            ],
            self::TCN => [
                'trackingInfo' => [
                    [
                        'trackingNumberInfo' => [
                            'trackingNumber' => $value,
                        ],
                    ],
                ],
                'includeDetailedScans' => $additionalParams['includeDetailedScans'] ?? false,
            ],
            self::REFERENCE_NUMBER => [
                'trackingInfo' => [
                    [
                        'referenceNumberInfo' => [
                            'referenceNumber' => $value,
                        ],
                        'shipDateBegin' => $additionalParams['shipDateBegin'] ?? null,
                        'shipDateEnd' => $additionalParams['shipDateEnd'] ?? null,
                    ],
                ],
                'includeDetailedScans' => $additionalParams['includeDetailedScans'] ?? false,
            ],
        };

        // Remove null values
        return $this->removeNullValues($payload);
    }

    /**
     * Recursively remove null values from array
     */
    private function removeNullValues(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->removeNullValues($value);
            } elseif ($value === null) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}
