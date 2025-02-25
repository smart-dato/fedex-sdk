<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\PaymentTypeEnum;

class PaymentPayload implements PayloadContract
{
    public function __construct(
        protected PaymentTypeEnum $paymentType,
        protected PayorPayload $payor,
    ) {}

    public function build(): array
    {
        $payload = [
            'paymentType' => $this->paymentType->value,
        ];

        if (! empty($this->payor)) {
            $payload['payor'] = $this->payor->build();
        }

        return $payload;
    }
}
