<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\ImageTypeEnum;
use SmartDato\FedEx\Enums\LabelFormatTypeEnum;
use SmartDato\FedEx\Enums\LabelOrderEnum;
use SmartDato\FedEx\Enums\LabelPrintingOrientationEnum;
use SmartDato\FedEx\Enums\LabelStockTypeEnum;

class LabelSpecificationPayload implements PayloadContract
{
    public function __construct(
        protected ImageTypeEnum $imageType,
        protected LabelStockTypeEnum $labelStockType,
        protected ?LabelFormatTypeEnum $labelFormatType = null,
        protected ?LabelPrintingOrientationEnum $labelPrintingOrientation = null,
        protected ?LabelOrderEnum $labelOrder = null,
        protected ?CustomerSpecifiedDetailPayload $customerSpecifiedDetail = null,
    ) {}

    public function build(): array
    {
        $payload = [
            'labelStockType' => $this->labelStockType->value,
            'imageType' => $this->imageType->value,
        ];

        if (! empty($this->labelFormatType)) {
            $payload['labelFormatType'] = $this->labelFormatType->value;
        }

        if (! empty($this->labelPrintingOrientation)) {
            $payload['labelPrintingOrientation'] = $this->labelPrintingOrientation->value;
        }

        if (! empty($this->labelOrder)) {
            $payload['labelOrder'] = $this->labelOrder->value;
        }

        if (! empty($this->customerSpecifiedDetail)) {
            $payload['customerSpecifiedDetail'] = $this->customerSpecifiedDetail->build();
        }

        return $payload;
    }
}
