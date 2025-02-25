<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;

class ContactPayload implements PayloadContract
{
    public function __construct(
        protected string $phoneNumber,
        protected ?string $personName = null,
        protected ?string $companyName = null,
    ) {}

    public function build(): array
    {
        $payload = [
            'phoneNumber' => $this->phoneNumber,
        ];

        if (! empty($this->personName)) {
            $payload['personName'] = $this->personName;
        }

        if (! empty($this->companyName)) {
            $payload['companyName'] = $this->companyName;
        }

        return $payload;
    }
}
