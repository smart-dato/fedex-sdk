<?php

use SmartDato\FedEx\Enums\CountryEnum;
use SmartDato\FedEx\Enums\ImageTypeEnum;
use SmartDato\FedEx\Enums\LabelResponseOptionEnum;
use SmartDato\FedEx\Enums\LabelStockTypeEnum;
use SmartDato\FedEx\Enums\PackagingTypeEnum;
use SmartDato\FedEx\Enums\PaymentTypeEnum;
use SmartDato\FedEx\Enums\PickupTypeEnum;
use SmartDato\FedEx\Enums\WeightUnitEnum;
use SmartDato\FedEx\Fedex;
use SmartDato\FedEx\Payloads\AddressPayload;
use SmartDato\FedEx\Payloads\ContactPayload;
use SmartDato\FedEx\Payloads\LabelSpecificationPayload;
use SmartDato\FedEx\Payloads\PaymentPayload;
use SmartDato\FedEx\Payloads\PayorPayload;
use SmartDato\FedEx\Payloads\RecipientPayload;
use SmartDato\FedEx\Payloads\RequestedPackageLineItemPayload;
use SmartDato\FedEx\Payloads\ShipmentPayload;
use SmartDato\FedEx\Payloads\ShipperPayload;
use SmartDato\FedEx\Payloads\WeightPayload;

it('can create a shipment', function () {
    $payload = new ShipmentPayload(
        pickupType: PickupTypeEnum::DROPOFF_AT_FEDEX_LOCATION,
        serviceType: 'STANDARD_OVERNIGHT',
        packagingType: PackagingTypeEnum::YOUR_PACKAGING,
        totalWeight: 20.6,
        shipper: new ShipperPayload(
            contact: new ContactPayload(
                phoneNumber: '9018328595',
                personName: 'SENDER NAME',
            ),
            address: new AddressPayload(
                streetLines: [
                    'SENDER ADDRESS 1',
                ],
                city: 'MEMPHIS',
                countryCode: CountryEnum::US,
                stateOrProvinceCode: 'TN',
                postalCode: '38116',
            )
        ),
        recipients: [
            new RecipientPayload(
                contact: new ContactPayload(
                    phoneNumber: '9018328595',
                    personName: 'RECIPIENT NAME',
                ),
                address: new AddressPayload(
                    streetLines: [
                        'RECIPIENT ADDRESS 1',
                    ],
                    city: 'MEMPHIS',
                    countryCode: CountryEnum::US,
                    stateOrProvinceCode: 'TN',
                    postalCode: '38116',
                )
            ),
        ],
        shippingChargesPayment: new PaymentPayload(
            paymentType: PaymentTypeEnum::SENDER,
            payor: new PayorPayload(
                accountNumber: '12XXXXX89',
                address: new AddressPayload(
                    streetLines: [
                        'SENDER  ADDRESS 1',
                    ],
                    city: 'MEMPHIS',
                    countryCode: CountryEnum::US,
                    stateOrProvinceCode: 'TN',
                    postalCode: '38116',
                )
            )
        ),
        labelSpecification: new LabelSpecificationPayload(
            imageType: ImageTypeEnum::PDF,
            labelStockType: LabelStockTypeEnum::STOCK_4X6,
        ),
        requestedPackageLineItems: [
            new RequestedPackageLineItemPayload(
                weight: new WeightPayload(
                    units: WeightUnitEnum::KG,
                    value: 20,
                )
            ),
        ],
    );

    $client = new \SmartDato\FedEx\Auth\OAuthClient(
        baseUrl: 'https://apis-sandbox.fedex.com',
        clientId: 'YOUR_CLIENT_ID',
        clientSecret: 'YOUR_CLIENT_SECRET',
    );

    $data = (new Fedex(
        baseUrl: 'https://apis-sandbox.fedex.com',
        oauthClient: $client,
        labelResponseOptions: LabelResponseOptionEnum::URL_ONLY,
        accountNumber: 'XXXXX2842',
    ))->createShipment($payload);

    expect($data)->not()->toBeNull()
        ->and($data['errors'])->toBeNull();
})->skip();
