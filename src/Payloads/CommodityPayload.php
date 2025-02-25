<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\CountryEnum;
use SmartDato\FedEx\Enums\QuantityUnitEnum;

class CommodityPayload implements PayloadContract
{
    public function __construct(
        protected ?int $numberOfPieces = null,
        protected ?string $exportLicenseNumber = null,
        protected ?string $description = null,
        protected ?CountryEnum $countryOfManufacture = null,
        protected ?WeightPayload $weight = null,
        protected ?int $quantity = null,
        protected ?QuantityUnitEnum $quantityUnits = null,
        protected ?ValuePayload $unitPrice = null,
        protected ?ValuePayload $customsValue = null,
    ) {}

    public function build(): array
    {
        $payload = [
            'description' => $this->description,
        ];

        if (! empty($this->numberOfPieces)) {
            $payload['numberOfPieces'] = $this->numberOfPieces;
        }

        if (! empty($this->exportLicenseNumber)) {
            $payload['exportLicenseNumber'] = $this->exportLicenseNumber;
        }

        if (! empty($this->description)) {
            $payload['description'] = $this->description;
        }

        if (! empty($this->countryOfManufacture)) {
            $payload['countryOfManufacture'] = $this->countryOfManufacture->value;
        }

        if (! empty($this->weight)) {
            $payload['weight'] = $this->weight->build();
        }

        if (! empty($this->quantity)) {
            $payload['quantity'] = $this->quantity;
        }

        if (! empty($this->quantityUnits)) {
            $payload['quantityUnits'] = $this->quantityUnits->value;
        }

        if (! empty($this->unitPrice)) {
            $payload['unitPrice'] = $this->unitPrice->build();
        }

        if (! empty($this->customsValue)) {
            $payload['customsValue'] = $this->customsValue->build();
        }

        return $payload;
    }
}
