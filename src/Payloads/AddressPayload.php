<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\CountryEnum;

class AddressPayload implements PayloadContract
{
    public function __construct(
        protected array $streetLines, /** @var string[] $streetLines */
        protected string $city,
        protected CountryEnum $countryCode,
        protected ?string $stateOrProvinceCode = null,
        protected ?string $postalCode = null,
        protected ?bool $residential = null,
    ) {}

    public function build(): array
    {
        $payload = [
            'streetLines' => $this->streetLines,
            'city' => $this->city,
            'countryCode' => $this->countryCode->value,
        ];

        if (! empty($this->stateOrProvinceCode)) {
            $payload['stateOrProvinceCode'] = $this->stateOrProvinceCode;
        }

        if (! empty($this->postalCode)) {
            $payload['postalCode'] = $this->postalCode;
        }

        if (! empty($this->residential)) {
            $payload['residential'] = $this->residential;
        }

        return $payload;
    }
}
