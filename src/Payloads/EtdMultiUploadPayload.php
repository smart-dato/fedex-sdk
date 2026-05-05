<?php

namespace SmartDato\FedEx\Payloads;

use Illuminate\Support\Carbon;
use InvalidArgumentException;
use SmartDato\FedEx\Contracts\PayloadContract;
use SmartDato\FedEx\Enums\CarrierCodeEnum;
use SmartDato\FedEx\Enums\CountryEnum;
use SmartDato\FedEx\Enums\EtdWorkflowEnum;

class EtdMultiUploadPayload implements PayloadContract
{
    /**
     * @param  EtdMultiMetaPayload[]  $metaData
     */
    public function __construct(
        protected EtdWorkflowEnum $workflowName,
        protected CarrierCodeEnum $carrierCode,
        protected CountryEnum $originCountryCode,
        protected CountryEnum $destinationCountryCode,
        protected array $metaData,
        protected ?Carbon $shipmentDate = null,
        protected ?string $trackingNumber = null,
    ) {
        if (count($this->metaData) > 5) {
            throw new InvalidArgumentException('A maximum of 5 documents can be uploaded per request.');
        }
    }

    public function build(): array
    {
        $payload = [
            'workflowName' => $this->workflowName->value,
            'carrierCode' => $this->carrierCode->value,
            'originCountryCode' => $this->originCountryCode->value,
            'destinationCountryCode' => $this->destinationCountryCode->value,
            'metaData' => array_map(
                fn (EtdMultiMetaPayload $item) => $item->build(),
                $this->metaData
            ),
        ];

        if ($this->shipmentDate !== null) {
            $payload['shipmentDate'] = $this->shipmentDate->format('Y-m-d\TH:i:s');
        }

        if (! empty($this->trackingNumber)) {
            $payload['trackingNumber'] = $this->trackingNumber;
        }

        return $payload;
    }

    /**
     * @return EtdMultiMetaPayload[]
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }
}
