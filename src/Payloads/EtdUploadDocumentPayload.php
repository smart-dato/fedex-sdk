<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\CarrierCodeEnum;
use SmartDato\FedEx\Enums\EtdContentTypeEnum;
use SmartDato\FedEx\Enums\EtdWorkflowEnum;

class EtdUploadDocumentPayload implements PayloadContract
{
    public function __construct(
        protected EtdWorkflowEnum $workflowName,
        protected string $fileName,
        protected EtdContentTypeEnum $contentType,
        protected EtdMetaPayload $meta,
        protected ?CarrierCodeEnum $carrierCode = null,
    ) {}

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function build(): array
    {
        $payload = [
            'workflowName' => $this->workflowName->value,
            'name' => $this->fileName,
            'contentType' => $this->contentType->value,
            'meta' => $this->meta->build(),
        ];

        if (! empty($this->carrierCode)) {
            $payload['carrierCode'] = $this->carrierCode->value;
        }

        return $payload;
    }
}
