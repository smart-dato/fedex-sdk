<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\WeightUnitEnum;

class WeightPayload implements PayloadContract
{
    public function __construct(
        protected WeightUnitEnum $units,
        protected float $value
    ) {}

    public function build(): array
    {
        return [
            'units' => $this->units->value,
            'value' => $this->value,
        ];
    }
}
