<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\LhsImageContentTypeEnum;
use SmartDato\FedEx\Enums\LhsImageIndexEnum;
use SmartDato\FedEx\Enums\LhsImageTypeEnum;

class LhsImageUploadPayload implements PayloadContract
{
    public function __construct(
        protected string $referenceId,
        protected string $name,
        protected LhsImageContentTypeEnum $contentType,
        protected LhsImageTypeEnum $imageType,
        protected LhsImageIndexEnum $imageIndex,
    ) {}

    public function build(): array
    {
        return [
            'document' => [
                'referenceId' => $this->referenceId,
                'name' => $this->name,
                'contentType' => $this->contentType->value,
                'meta' => [
                    'imageType' => $this->imageType->value,
                    'imageIndex' => $this->imageIndex->value,
                ],
            ],
            'rules' => [
                'workflowName' => 'LetterheadSignature',
            ],
        ];
    }
}
