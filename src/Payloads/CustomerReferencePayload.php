<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\CustomerReferenceTypeEnum;

class CustomerReferencePayload implements PayloadContract
{
    public function __construct(
        protected CustomerReferenceTypeEnum $customerReferenceType,
        protected string $value,
    ) {}

    public function build(): array
    {
        return [
            'customerReferenceType' => $this->customerReferenceType->value,
            'value' => $this->value,
        ];
    }
}
