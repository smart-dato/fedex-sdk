<?php

namespace SmartDato\FedEx\Payloads;

use SmartDato\FedEx\Contracts\PayloadContract;

class RequestedPackageLineItemPayload implements PayloadContract
{
    public function __construct(
        protected WeightPayload $weight,
        protected ?int $sequenceNumber = null,
        protected ?int $groupPackageCount = null,
        protected ?DimensionsPayload $dimensions = null,
        protected ?array $customerReferences = null, /** @var CustomerReferencePayload[] $customerReferences */
    ) {}

    public function build(): array
    {
        $payload = [
            'weight' => $this->weight->build(),
        ];

        if (! empty($this->sequenceNumber)) {
            $payload['sequenceNumber'] = $this->sequenceNumber;
        }

        if (! empty($this->groupPackageCount)) {
            $payload['groupPackageCount'] = $this->groupPackageCount;
        }

        if (! empty($this->dimensions)) {
            $payload['dimensions'] = $this->dimensions->build();
        }

        if (! empty($this->customerReferences)) {
            $payload['customerReferences'] = array_map(
                fn (CustomerReferencePayload $customerReference) => $customerReference->build(),
                $this->customerReferences
            );
        }

        return $payload;
    }
}
