<?php

use SmartDato\FedEx\Enums\DocumentTypeEnum;
use SmartDato\FedEx\Enums\ShipmentSpecialServiceTypeEnum;
use SmartDato\FedEx\Enums\WeightUnitEnum;
use SmartDato\FedEx\Payloads\DocumentPayload;
use SmartDato\FedEx\Payloads\EtdDetailPayload;
use SmartDato\FedEx\Payloads\ShipmentDryIceDetailPayload;
use SmartDato\FedEx\Payloads\ShipmentSpecialServicesPayload;
use SmartDato\FedEx\Payloads\WeightPayload;

function etdDetail(): EtdDetailPayload
{
    return new EtdDetailPayload([
        new DocumentPayload(
            documentType: DocumentTypeEnum::COMMERCIAL_INVOICE,
            documentReference: 'REF-1',
            description: 'Invoice',
            documentId: 'doc-1',
        ),
    ]);
}

function dryIceDetail(): ShipmentDryIceDetailPayload
{
    return new ShipmentDryIceDetailPayload(
        totalWeight: new WeightPayload(units: WeightUnitEnum::KG, value: 2.5),
        packageCount: 1,
    );
}

it('sends only the trade documents detail for electronic trade documents', function () {
    $payload = new ShipmentSpecialServicesPayload(
        specialServiceTypes: [ShipmentSpecialServiceTypeEnum::ELECTRONIC_TRADE_DOCUMENTS],
        etdDetail: etdDetail(),
    );

    expect($payload->build())->toBe([
        'specialServiceTypes' => ['ELECTRONIC_TRADE_DOCUMENTS'],
        'etdDetail' => [
            'attachedDocuments' => [[
                'documentType' => 'COMMERCIAL_INVOICE',
                'documentReference' => 'REF-1',
                'description' => 'Invoice',
                'documentId' => 'doc-1',
            ]],
        ],
    ]);
});

it('sends only the dry ice detail for dry ice', function () {
    $payload = new ShipmentSpecialServicesPayload(
        specialServiceTypes: [ShipmentSpecialServiceTypeEnum::DRY_ICE],
        shipmentDryIceDetail: dryIceDetail(),
    );

    expect($payload->build())->toBe([
        'specialServiceTypes' => ['DRY_ICE'],
        'shipmentDryIceDetail' => [
            'totalWeight' => ['units' => 'KG', 'value' => 2.5],
            'packageCount' => 1,
        ],
    ]);
});

it('sends both details when both are given', function () {
    $payload = new ShipmentSpecialServicesPayload(
        [ShipmentSpecialServiceTypeEnum::ELECTRONIC_TRADE_DOCUMENTS, ShipmentSpecialServiceTypeEnum::DRY_ICE],
        etdDetail(),
        dryIceDetail(),
    );

    expect($payload->build())->toHaveKeys(['specialServiceTypes', 'etdDetail', 'shipmentDryIceDetail']);
});

it('sends no detail when none is given', function () {
    $payload = new ShipmentSpecialServicesPayload(
        specialServiceTypes: [ShipmentSpecialServiceTypeEnum::DRY_ICE],
    );

    expect($payload->build())->toBe(['specialServiceTypes' => ['DRY_ICE']]);
});

it('builds a trade documents detail without documents', function () {
    expect((new EtdDetailPayload)->build())->toBe([]);
});
