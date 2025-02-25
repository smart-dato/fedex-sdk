<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\LengthUnitEnum;

class DimensionsPayload implements PayloadContract
{
    public function __construct(
        protected int $length,
        protected int $width,
        protected int $height,
        protected LengthUnitEnum $units,
    ) {}

    public function build(): array
    {
        return [
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'units' => $this->units->value,
        ];
    }
}
