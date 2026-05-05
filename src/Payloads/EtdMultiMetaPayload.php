<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\EtdContentTypeEnum;
use SmartDato\FedEx\Enums\ShipDocumentTypeEnum;

class EtdMultiMetaPayload implements PayloadContract
{
    public function __construct(
        protected string $fileName,
        protected EtdContentTypeEnum $contentType,
        protected ShipDocumentTypeEnum $shipDocumentType,
        protected string $filePath,
        protected ?string $fileReferenceId = null,
        protected ?string $formCode = null,
        protected ?string $originLocationCode = null,
        protected ?string $destinationLocationCode = null,
    ) {}

    public function build(): array
    {
        $payload = [
            'fileName' => $this->fileName,
            'contentType' => $this->contentType->value,
            'shipDocumentType' => $this->shipDocumentType->value,
        ];

        if (! empty($this->fileReferenceId)) {
            $payload['fileReferenceId'] = $this->fileReferenceId;
        }

        if (! empty($this->formCode)) {
            $payload['formCode'] = $this->formCode;
        }

        if (! empty($this->originLocationCode)) {
            $payload['originLocationCode'] = $this->originLocationCode;
        }

        if (! empty($this->destinationLocationCode)) {
            $payload['destinationLocationCode'] = $this->destinationLocationCode;
        }

        return $payload;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
