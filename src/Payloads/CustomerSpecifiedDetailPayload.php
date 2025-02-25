<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\MaskedDataEnum;

class CustomerSpecifiedDetailPayload implements PayloadContract
{
    public function __construct(
        protected array $maskedData, /** @var MaskedDataEnum[] $maskedData */
    ) {}

    public function build(): array
    {
        return [
            'maskedData' => array_map(
                fn (MaskedDataEnum $maskedData) => $maskedData->value,
                $this->maskedData,
            ),
        ];
    }
}
