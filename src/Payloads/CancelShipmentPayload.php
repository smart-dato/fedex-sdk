<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\DeletionControlEnum;

class CancelShipmentPayload implements PayloadContract
{
    public function __construct(
        protected string $trackingNumber,
        protected ?string $scheduledDate = null,
        protected ?bool $emailShipment = null,
        protected ?string $senderEmailAddress = null,
        protected DeletionControlEnum $deletionControl = DeletionControlEnum::DELETE_ALL_PACKAGES,
    ) {}

    public function build(): array
    {
        $payload = [
            'trackingNumber' => $this->trackingNumber,
            'deletionControl' => $this->deletionControl->value,
        ];

        if ($this->scheduledDate !== null) {
            $payload['scheduledDate'] = $this->scheduledDate;
        }

        if ($this->emailShipment !== null) {
            $payload['emailShipment'] = $this->emailShipment;
        }

        if ($this->senderEmailAddress !== null) {
            $payload['senderEmailAddress'] = $this->senderEmailAddress;
        }

        return $payload;
    }
}
