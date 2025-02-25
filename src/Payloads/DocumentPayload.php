<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\DocumentTypeEnum;

class DocumentPayload implements PayloadContract
{
    public function __construct(
        protected DocumentTypeEnum $documentType,
        protected string $documentReference,
        protected string $description,
        protected string $documentId,
    ) {}

    public function build(): array
    {
        return [
            'documentType' => $this->documentType->value,
            'documentReference' => $this->documentReference,
            'description' => $this->description,
            'documentId' => $this->documentId,
        ];
    }
}
