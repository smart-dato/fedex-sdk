<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;

class RecipientPayload implements PayloadContract
{
    public function __construct(
        protected ContactPayload $contact,
        protected AddressPayload $address,
    ) {}

    public function build(): array
    {
        return [
            'contact' => $this->contact->build(),
            'address' => $this->address->build(),
        ];
    }
}
