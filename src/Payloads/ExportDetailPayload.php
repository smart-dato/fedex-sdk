<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\B13AFilingOptionEnum;

class ExportDetailPayload implements PayloadContract
{
    public function __construct(
        protected B13AFilingOptionEnum $b13AFilingOption,
    ) {}

    public function build(): array
    {
        return [
            'b13AFilingOption' => $this->b13AFilingOption->value,
        ];
    }
}
