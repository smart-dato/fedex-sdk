<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;

class EtdDetailPayload implements PayloadContract
{
    public function __construct(
        protected ?array $attachedDocuments = null, /** @var DocumentPayload[] $attachedDocuments */
    ) {}

    public function build(): array
    {
        return [
            'attachedDocuments' => array_map(
                fn (DocumentPayload $attachedDocument) => $attachedDocument->build(),
                $this->attachedDocuments
            ),
        ];
    }
}
