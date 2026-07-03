<?php

use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use SmartDato\FedEx\Auth\OAuthClient;
use SmartDato\FedEx\Enums\CarrierCodeEnum;
use SmartDato\FedEx\Enums\CountryEnum;
use SmartDato\FedEx\Enums\EtdContentTypeEnum;
use SmartDato\FedEx\Enums\EtdWorkflowEnum;
use SmartDato\FedEx\Enums\LhsImageContentTypeEnum;
use SmartDato\FedEx\Enums\LhsImageIndexEnum;
use SmartDato\FedEx\Enums\LhsImageTypeEnum;
use SmartDato\FedEx\Enums\ShipDocumentTypeEnum;
use SmartDato\FedEx\Fedex;
use SmartDato\FedEx\Payloads\EtdMetaPayload;
use SmartDato\FedEx\Payloads\EtdMultiMetaPayload;
use SmartDato\FedEx\Payloads\EtdMultiUploadPayload;
use SmartDato\FedEx\Payloads\EtdUploadDocumentPayload;
use SmartDato\FedEx\Payloads\LhsImageUploadPayload;
use SmartDato\FedEx\TradeDocuments\TradeDocumentsClient;

it('builds an ETD pre-shipment upload payload', function () {
    $payload = new EtdUploadDocumentPayload(
        workflowName: EtdWorkflowEnum::PRE_SHIPMENT,
        fileName: 'invoice.pdf',
        contentType: EtdContentTypeEnum::PDF,
        meta: new EtdMetaPayload(
            shipDocumentType: ShipDocumentTypeEnum::COMMERCIAL_INVOICE,
            originCountryCode: CountryEnum::US,
            destinationCountryCode: CountryEnum::CA,
        ),
    );

    expect($payload->build())->toEqual([
        'workflowName' => 'ETDPreshipment',
        'name' => 'invoice.pdf',
        'contentType' => 'application/pdf',
        'meta' => [
            'shipDocumentType' => 'COMMERCIAL_INVOICE',
            'originCountryCode' => 'US',
            'destinationCountryCode' => 'CA',
        ],
    ]);
});

it('builds an ETD post-shipment upload payload with all optional fields', function () {
    $payload = new EtdUploadDocumentPayload(
        workflowName: EtdWorkflowEnum::POST_SHIPMENT,
        fileName: 'invoice.pdf',
        contentType: EtdContentTypeEnum::PDF,
        carrierCode: CarrierCodeEnum::FDXE,
        meta: new EtdMetaPayload(
            shipDocumentType: ShipDocumentTypeEnum::USMCA_CERTIFICATION_OF_ORIGIN,
            originCountryCode: CountryEnum::US,
            destinationCountryCode: CountryEnum::CA,
            formCode: 'USMCA',
            trackingNumber: '794791292805',
            shipmentDate: Carbon::parse('2024-01-06T00:00:00'),
            originLocationCode: 'GVTKK',
            destinationLocationCode: 'JNUA',
        ),
    );

    expect($payload->build())->toEqual([
        'workflowName' => 'ETDPostshipment',
        'name' => 'invoice.pdf',
        'contentType' => 'application/pdf',
        'meta' => [
            'shipDocumentType' => 'USMCA_CERTIFICATION_OF_ORIGIN',
            'originCountryCode' => 'US',
            'destinationCountryCode' => 'CA',
            'formCode' => 'USMCA',
            'trackingNumber' => '794791292805',
            'shipmentDate' => '2024-01-06T00:00:00',
            'originLocationCode' => 'GVTKK',
            'destinationLocationCode' => 'JNUA',
        ],
        'carrierCode' => 'FDXE',
    ]);
});

it('builds a multi-upload payload', function () {
    $payload = new EtdMultiUploadPayload(
        workflowName: EtdWorkflowEnum::PRE_SHIPMENT,
        carrierCode: CarrierCodeEnum::FDXE,
        originCountryCode: CountryEnum::US,
        destinationCountryCode: CountryEnum::CA,
        metaData: [
            new EtdMultiMetaPayload(
                fileName: 'file1.png',
                contentType: EtdContentTypeEnum::PNG,
                shipDocumentType: ShipDocumentTypeEnum::COMMERCIAL_INVOICE,
                filePath: '/tmp/file1.png',
                fileReferenceId: 'CI_1',
                formCode: 'USMCA',
            ),
        ],
    );

    expect($payload->build())->toEqual([
        'workflowName' => 'ETDPreshipment',
        'carrierCode' => 'FDXE',
        'originCountryCode' => 'US',
        'destinationCountryCode' => 'CA',
        'metaData' => [
            [
                'fileName' => 'file1.png',
                'contentType' => 'image/png',
                'shipDocumentType' => 'COMMERCIAL_INVOICE',
                'fileReferenceId' => 'CI_1',
                'formCode' => 'USMCA',
            ],
        ],
    ]);
});

it('rejects more than 5 documents in a multi-upload payload', function () {
    $meta = fn (string $name) => new EtdMultiMetaPayload(
        fileName: $name,
        contentType: EtdContentTypeEnum::PDF,
        shipDocumentType: ShipDocumentTypeEnum::COMMERCIAL_INVOICE,
        filePath: "/tmp/{$name}",
    );

    new EtdMultiUploadPayload(
        workflowName: EtdWorkflowEnum::PRE_SHIPMENT,
        carrierCode: CarrierCodeEnum::FDXE,
        originCountryCode: CountryEnum::US,
        destinationCountryCode: CountryEnum::CA,
        metaData: array_map($meta, ['a', 'b', 'c', 'd', 'e', 'f']),
    );
})->throws(InvalidArgumentException::class);

it('builds a letterhead/signature upload payload', function () {
    $payload = new LhsImageUploadPayload(
        referenceId: '1234',
        name: 'LH2.PNG',
        contentType: LhsImageContentTypeEnum::PNG,
        imageType: LhsImageTypeEnum::SIGNATURE,
        imageIndex: LhsImageIndexEnum::IMAGE_1,
    );

    expect($payload->build())->toEqual([
        'document' => [
            'referenceId' => '1234',
            'name' => 'LH2.PNG',
            'contentType' => 'image/png',
            'meta' => [
                'imageType' => 'SIGNATURE',
                'imageIndex' => 'IMAGE_1',
            ],
        ],
        'rules' => [
            'workflowName' => 'LetterheadSignature',
        ],
    ]);
});

it('exposes the Trade Documents sub-client from the Fedex service', function () {
    $fedex = new Fedex(
        oauthClient: new OAuthClient(
            baseUrl: 'https://apis-sandbox.fedex.com',
            clientId: 'id',
            clientSecret: 'secret',
        ),
        config: [
            'environment' => 'sandbox',
            'base_url' => ['sandbox' => 'https://apis-sandbox.fedex.com'],
            'account_number' => 'XXXXX2842',
        ],
    );

    expect($fedex->tradeDocuments())->toBeInstanceOf(TradeDocumentsClient::class)
        ->and($fedex->tradeDocuments())->toBe($fedex->tradeDocuments());
});

it('resolves the dedicated document host separately from the main API host', function (string $environment, string $apiHost, string $documentHost) {
    $fedex = new Fedex(
        oauthClient: new OAuthClient(
            baseUrl: $apiHost,
            clientId: 'id',
            clientSecret: 'secret',
        ),
        config: [
            'environment' => $environment,
            'base_url' => [$environment => $apiHost],
            'account_number' => 'XXXXX2842',
        ],
    );

    expect($fedex->getDocumentBaseUrl())->toBe($documentHost)
        ->and($fedex->getBaseUrl())->toBe($apiHost);
})->with([
    'production' => ['production', 'https://apis.fedex.com', 'https://documentapi.prod.fedex.com'],
    'sandbox' => ['sandbox', 'https://apis-sandbox.fedex.com', 'https://documentapitest.prod.fedex.com/sandbox'],
]);

it('attaches the file under the payload declared name, not the storage basename', function () {
    Http::fake([
        '*/oauth/token' => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
        'documentapi.prod.fedex.com/*' => Http::response(['output' => ['meta' => ['docId' => '123']]]),
    ]);

    $fedex = new Fedex(
        oauthClient: new OAuthClient(
            baseUrl: 'https://apis.fedex.com',
            clientId: 'id',
            clientSecret: 'secret',
        ),
        config: [
            'environment' => 'production',
            'base_url' => ['production' => 'https://apis.fedex.com'],
            'account_number' => 'XXXXX2842',
        ],
    );

    $file = tempnam(sys_get_temp_dir(), 'hashedstoragename');
    file_put_contents($file, '%PDF-1.4 test');

    $payload = new EtdUploadDocumentPayload(
        workflowName: EtdWorkflowEnum::PRE_SHIPMENT,
        fileName: 'invoice.pdf',
        contentType: EtdContentTypeEnum::PDF,
        meta: new EtdMetaPayload(
            shipDocumentType: ShipDocumentTypeEnum::COMMERCIAL_INVOICE,
            originCountryCode: CountryEnum::US,
            destinationCountryCode: CountryEnum::CA,
        ),
    );

    $fedex->tradeDocuments()->upload($payload, $file);

    Http::assertSent(function ($request) use ($file) {
        if (! str_contains($request->url(), '/documents/v1/etds/upload')) {
            return false;
        }

        return str_contains($request->body(), 'filename="invoice.pdf"')
            && ! str_contains($request->body(), basename($file));
    });

    @unlink($file);
});

it('returns the raw response so consumers can persist body and decode json', function () {
    Http::fake([
        '*/oauth/token' => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
        'example.test/*' => Http::response(['output' => ['meta' => ['docId' => 'doc-1']]], 200),
    ]);

    $oauth = new OAuthClient(baseUrl: 'https://example.test', clientId: 'id', clientSecret: 'secret');
    $client = new TradeDocumentsClient($oauth, 'https://example.test');

    $file = tempnam(sys_get_temp_dir(), 'etd');
    file_put_contents($file, '%PDF-1.4 test');

    $payload = new EtdUploadDocumentPayload(
        workflowName: EtdWorkflowEnum::PRE_SHIPMENT,
        fileName: 'invoice.pdf',
        contentType: EtdContentTypeEnum::PDF,
        meta: new EtdMetaPayload(
            shipDocumentType: ShipDocumentTypeEnum::COMMERCIAL_INVOICE,
            originCountryCode: CountryEnum::US,
            destinationCountryCode: CountryEnum::CA,
        ),
    );

    $response = $client->upload($payload, $file);

    @unlink($file);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->status())->toBe(200)
        ->and($response->body())->toBe(json_encode(['output' => ['meta' => ['docId' => 'doc-1']]]))
        ->and($response->json('output.meta.docId'))->toBe('doc-1');
});

it('sends trade document uploads to the dedicated document host, not the main API host', function () {
    Http::fake([
        '*/oauth/token' => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
        'documentapi.prod.fedex.com/*' => Http::response(['output' => ['documentStatuses' => [['documentId' => '123']]]]),
    ]);

    $fedex = new Fedex(
        oauthClient: new OAuthClient(
            baseUrl: 'https://apis.fedex.com',
            clientId: 'id',
            clientSecret: 'secret',
        ),
        config: [
            'environment' => 'production',
            'base_url' => ['production' => 'https://apis.fedex.com'],
            'account_number' => 'XXXXX2842',
        ],
    );

    $file = tempnam(sys_get_temp_dir(), 'etd');
    file_put_contents($file, '%PDF-1.4 test');

    $payload = new EtdUploadDocumentPayload(
        workflowName: EtdWorkflowEnum::PRE_SHIPMENT,
        fileName: 'invoice.pdf',
        contentType: EtdContentTypeEnum::PDF,
        meta: new EtdMetaPayload(
            shipDocumentType: ShipDocumentTypeEnum::COMMERCIAL_INVOICE,
            originCountryCode: CountryEnum::US,
            destinationCountryCode: CountryEnum::CA,
        ),
    );

    $fedex->tradeDocuments()->upload($payload, $file);

    @unlink($file);

    Http::assertSent(fn ($request) => $request->url() === 'https://documentapi.prod.fedex.com/documents/v1/etds/upload');
});
