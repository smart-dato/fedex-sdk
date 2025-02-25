<?php

namespace SmartDato\FedEx\Payloads;

use Illuminate\Support\Carbon;
use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\PackagingTypeEnum;
use SmartDato\FedEx\Enums\PickupTypeEnum;

class ShipmentPayload implements PayloadContract
{
    public function __construct(
        protected PickupTypeEnum $pickupType, // 'REGULAR_PICKUP',
        protected string $serviceType,
        protected PackagingTypeEnum $packagingType, // 'YOUR_PACKAGING'
        protected float $totalWeight,
        protected ShipperPayload $shipper,
        protected array $recipients, /** @var RecipientPayload[] $recipients */
        protected PaymentPayload $shippingChargesPayment,
        protected LabelSpecificationPayload $labelSpecification,
        protected array $requestedPackageLineItems, /** @var RequestedPackageLineItemPayload[] $requestedPackageLineItems */
        protected ?Carbon $shipDatestamp = null,
        protected ?ValuePayload $totalDeclaredValue = null,
        protected ?CustomsClearanceDetailPayload $customsClearanceDetail = null,
        protected ?int $totalPackageCount = null,
        protected ?ShipmentSpecialServicesPayload $shipmentSpecialServices = null,
    ) {}

    public function build(): array
    {
        $payload = [
            'pickupType' => $this->pickupType->value,
            'serviceType' => $this->serviceType,
            'packagingType' => $this->packagingType->value,
            'totalWeight' => $this->totalWeight,
            'shipper' => $this->shipper->build(),
            'recipients' => array_map(
                fn (RecipientPayload $recipient) => $recipient->build(),
                $this->recipients
            ),
            'shippingChargesPayment' => $this->shippingChargesPayment->build(),
            'labelSpecification' => $this->labelSpecification->build(),
            'requestedPackageLineItems' => array_map(
                fn (RequestedPackageLineItemPayload $requestedPackageLineItems) => $requestedPackageLineItems->build(),
                $this->requestedPackageLineItems
            ),
        ];

        if (! empty($this->shipDatestamp)) {
            $payload['shipDatestamp'] = $this->shipDatestamp->format('c');
        }

        if (! empty($this->totalDeclaredValue)) {
            $payload['totalDeclaredValue'] = $this->totalDeclaredValue->build();
        }

        if (! empty($this->customsClearanceDetail)) {
            $payload['customsClearanceDetail'] = $this->customsClearanceDetail->build();
        }

        if (! empty($this->totalPackageCount)) {
            $payload['totalPackageCount'] = $this->totalPackageCount;
        }

        if (! empty($this->shipmentSpecialServices)) {
            $payload['shipmentSpecialServices'] = $this->shipmentSpecialServices->build();
        }

        return $payload;
    }
}
