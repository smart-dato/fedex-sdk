<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;

class CustomsClearanceDetailPayload implements PayloadContract
{
    public function __construct(
        protected CommercialInvoicePayload $commercialInvoice,
        protected array $commodities, /** @var CommodityPayload[] $commodities */
        protected ?PaymentPayload $dutiesPayment = null,
        protected ?bool $isDocumentOnly = null,
        protected ?ValuePayload $totalCustomsValue = null,
        protected ?ExportDetailPayload $exportDetail = null,
    ) {}

    public function build(): array
    {
        $payload = [
            'commercialInvoice' => $this->commercialInvoice->build(),
            'commodities' => array_map(
                fn (CommodityPayload $commodity) => $commodity->build(),
                $this->commodities
            ),
        ];

        if ($this->dutiesPayment) {
            $payload['dutiesPayment'] = $this->dutiesPayment->build();
        }

        if ($this->isDocumentOnly) {
            $payload['isDocumentOnly'] = $this->isDocumentOnly;
        }

        if ($this->totalCustomsValue) {
            $payload['totalCustomsValue'] = $this->totalCustomsValue->build();
        }

        if ($this->exportDetail) {
            $payload['exportDetail'] = $this->exportDetail->build();
        }

        return $payload;
    }
}
