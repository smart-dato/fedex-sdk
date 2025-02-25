<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\CurrencyEnum;

class ValuePayload implements PayloadContract
{
    public function __construct(
        protected CurrencyEnum $currency,
        protected float $amount
    ) {}

    public function build(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency->value,
        ];
    }
}
