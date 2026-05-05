<?php

namespace SmartDato\FedEx\Payloads;

use Illuminate\Support\Carbon;
use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\CountryEnum;
use SmartDato\FedEx\Enums\ShipDocumentTypeEnum;

class EtdMetaPayload implements PayloadContract
{
    public function __construct(
        protected ShipDocumentTypeEnum $shipDocumentType,
        protected CountryEnum $originCountryCode,
        protected CountryEnum $destinationCountryCode,
        protected ?string $formCode = null,
        protected ?string $trackingNumber = null,
        protected ?Carbon $shipmentDate = null,
        protected ?string $originLocationCode = null,
        protected ?string $destinationLocationCode = null,
    ) {}

    public function build(): array
    {
        $payload = [
            'shipDocumentType' => $this->shipDocumentType->value,
            'originCountryCode' => $this->originCountryCode->value,
            'destinationCountryCode' => $this->destinationCountryCode->value,
        ];

        if (! empty($this->formCode)) {
            $payload['formCode'] = $this->formCode;
        }

        if (! empty($this->trackingNumber)) {
            $payload['trackingNumber'] = $this->trackingNumber;
        }

        if ($this->shipmentDate !== null) {
            $payload['shipmentDate'] = $this->shipmentDate->format('Y-m-d\TH:i:s');
        }

        if (! empty($this->originLocationCode)) {
            $payload['originLocationCode'] = $this->originLocationCode;
        }

        if (! empty($this->destinationLocationCode)) {
            $payload['destinationLocationCode'] = $this->destinationLocationCode;
        }

        return $payload;
    }
}
