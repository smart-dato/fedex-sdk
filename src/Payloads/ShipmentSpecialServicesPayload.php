<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\ShipmentSpecialServiceTypeEnum;

class ShipmentSpecialServicesPayload implements PayloadContract
{
    public function __construct(
        protected array $specialServiceTypes, /** @var ShipmentSpecialServiceTypeEnum[] $specialServiceTypes */
        protected ?EtdDetailPayload $etdDetail = null,
        protected ?ShipmentDryIceDetailPayload $shipmentDryIceDetail = null,
    ) {}

    public function build(): array
    {
        $payload = [];

        if (! empty($this->specialServiceTypes)) {
            $payload['specialServiceTypes'] = array_map(
                fn (ShipmentSpecialServiceTypeEnum $specialServiceType) => $specialServiceType->value,
                $this->specialServiceTypes
            );
        }

        if ($this->etdDetail !== null) {
            $payload['etdDetail'] = $this->etdDetail->build();
        }

        if ($this->shipmentDryIceDetail !== null) {
            $payload['shipmentDryIceDetail'] = $this->shipmentDryIceDetail->build();
        }

        return $payload;
    }
}
