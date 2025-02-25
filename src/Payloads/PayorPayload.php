<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;

class PayorPayload implements PayloadContract
{
    public function __construct(
        protected string $accountNumber,
        protected ?AddressPayload $address = null,
        protected ?ContactPayload $contact = null,
    ) {}

    public function build(): array
    {
        $payload = [
            'responsibleParty' => [
                'accountNumber' => [
                    'value' => $this->accountNumber,
                ],
            ],
        ];

        if (! empty($this->address)) {
            $payload['responsibleParty']['address'] = $this->address->build();
        }

        if (! empty($this->contact)) {
            $payload['responsibleParty']['contact'] = $this->contact->build();
        }

        return $payload;
    }
}
