<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;

class ShipmentDryIceDetailPayload implements PayloadContract
{
    public function __construct(
        protected WeightPayload $totalWeight,
        protected int $packageCount,
    ) {}

    public function build(): array
    {
        return [
            'totalWeight' => $this->totalWeight->build(),
            'packageCount' => $this->packageCount,
        ];
    }
}
