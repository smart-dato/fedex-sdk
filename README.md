# This is my package fedex-sdk

[![Latest Version on Packagist](https://img.shields.io/packagist/v/smart-dato/fedex-sdk.svg?style=flat-square)](https://packagist.org/packages/smart-dato/fedex-sdk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/smart-dato/fedex-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/smart-dato/fedex-sdk/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/smart-dato/fedex-sdk/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/smart-dato/fedex-sdk/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/smart-dato/fedex-sdk.svg?style=flat-square)](https://packagist.org/packages/smart-dato/fedex-sdk)

A comprehensive Laravel package for integrating with the FedEx REST API. This package provides OAuth 2.0 authentication, automatic token management, shipment creation, tracking, and more. Built with modern PHP practices and Laravel conventions.

## Installation

You can install the package via composer:

```bash
composer require smart-dato/fedex-sdk
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="fedex-config"
```

### Environment Variables

Add the following variables to your `.env` file:

```env
# FedEx Environment (sandbox or production)
FEDEX_ENVIRONMENT=sandbox

# FedEx OAuth Credentials
FEDEX_CLIENT_ID=your-client-id
FEDEX_CLIENT_SECRET=your-client-secret

# FedEx Account Number
FEDEX_ACCOUNT_NUMBER=your-account-number

# Optional: Label Response Options (URL_ONLY or LABEL)
FEDEX_LABEL_RESPONSE_OPTIONS=URL_ONLY

# Optional: Token Cache TTL in seconds (default: 3500)
FEDEX_TOKEN_CACHE_TTL=3500
```

### Getting FedEx API Credentials

1. Go to [FedEx Developer Portal](https://developer.fedex.com/)
2. Create an account or sign in
3. Create a new project
4. Generate API credentials (Client ID and Client Secret)
5. Use sandbox credentials for testing and production credentials for live operations

## Usage

### OAuth Authentication

The package handles OAuth authentication automatically. Tokens are cached to minimize API calls and automatically refreshed when needed.

```php
use SmartDato\FedEx\Fedex;

class ShippingController extends Controller
{
    public function __construct(private Fedex $fedex)
    {
    }

    public function createShipment()
    {
        // The OAuth token is automatically managed
        $result = $this->fedex->createShipment($shipmentPayload);
    }
}
```

#### Manual Token Management

If you need to manually manage tokens:

```php
use SmartDato\FedEx\Fedex;

public function __construct(private Fedex $fedex)
{
}

// Force refresh the OAuth token
$newToken = $this->fedex->refreshToken();

// Get the OAuth client directly
$oauthClient = $this->fedex->getOAuthClient();

// Get current access token
$token = $oauthClient->getAccessToken();

// Clear cached token
$oauthClient->clearToken();
```

### Creating a Shipment

```php
use SmartDato\FedEx\Fedex;
use SmartDato\FedEx\Payloads\ShipmentPayload;
use SmartDato\FedEx\Payloads\ShipperPayload;
use SmartDato\FedEx\Payloads\RecipientPayload;
use SmartDato\FedEx\Payloads\AddressPayload;
use SmartDato\FedEx\Payloads\ContactPayload;
use SmartDato\FedEx\Payloads\RequestedPackageLineItemPayload;
use SmartDato\FedEx\Payloads\WeightPayload;
use SmartDato\FedEx\Enums\WeightUnitEnum;
use SmartDato\FedEx\Enums\PackagingTypeEnum;
use SmartDato\FedEx\Enums\PickupTypeEnum;

$shipment = ShipmentPayload::make()
    ->setShipper(
        ShipperPayload::make()
            ->setContact(
                ContactPayload::make()
                    ->setPersonName('John Doe')
                    ->setPhoneNumber('1234567890')
            )
            ->setAddress(
                AddressPayload::make()
                    ->setStreetLines(['123 Main St'])
                    ->setCity('Memphis')
                    ->setStateOrProvinceCode('TN')
                    ->setPostalCode('38115')
                    ->setCountryCode('US')
            )
    )
    ->setRecipient(
        RecipientPayload::make()
            ->setContact(
                ContactPayload::make()
                    ->setPersonName('Jane Smith')
                    ->setPhoneNumber('0987654321')
            )
            ->setAddress(
                AddressPayload::make()
                    ->setStreetLines(['456 Oak Ave'])
                    ->setCity('Los Angeles')
                    ->setStateOrProvinceCode('CA')
                    ->setPostalCode('90001')
                    ->setCountryCode('US')
            )
    )
    ->setRequestedPackageLineItems([
        RequestedPackageLineItemPayload::make()
            ->setWeight(
                WeightPayload::make()
                    ->setValue(10.0)
                    ->setUnits(WeightUnitEnum::LB)
            )
    ])
    ->setPickupType(PickupTypeEnum::DROPOFF_AT_FEDEX_LOCATION)
    ->setPackagingType(PackagingTypeEnum::YOUR_PACKAGING);

// Inject or resolve the Fedex service
$fedex = app(Fedex::class);
$response = $fedex->createShipment($shipment);
```

### Tracking a Shipment

```php
use SmartDato\FedEx\Fedex;
use SmartDato\FedEx\Enums\TrackBy;

// Inject or resolve the Fedex service
$fedex = app(Fedex::class);

// Track by tracking number (default)
$tracking = $fedex->trackShipment('123456789012');

// Track by tracking number with detailed scans
$tracking = $fedex->trackShipment('123456789012', TrackBy::TRACKING_NUMBER, [
    'includeDetailedScans' => true,
]);

// Track by TCN (Tracking Control Number)
$tracking = $fedex->trackShipment('123456789012', TrackBy::TCN);

// Track by reference number with ship date range
$tracking = $fedex->trackShipment('REFERENCE123', TrackBy::REFERENCE_NUMBER, [
    'shipDateBegin' => '2024-01-01',
    'shipDateEnd' => '2024-01-31',
    'includeDetailedScans' => true,
]);

// Track multiple shipments at once
$tracking = $fedex->trackMultipleShipments([
    '123456789012',
    '123456789013',
    '123456789014',
], [
    'includeDetailedScans' => true,
]);
```

### OAuth Token Caching

The package automatically caches OAuth tokens using Laravel's cache system. By default:

- Tokens are cached for 3500 seconds (just under 1 hour)
- Cache key is `fedex_oauth_token`
- Tokens are automatically refreshed when expired

You can customize these settings in the config file or via environment variables.

### Error Handling

```php
use SmartDato\FedEx\Fedex;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;

$fedex = app(Fedex::class);

try {
    $result = $fedex->createShipment($shipmentPayload);
} catch (ConnectionException $e) {
    // Handle connection errors
    Log::error('FedEx API connection error: ' . $e->getMessage());
} catch (RuntimeException $e) {
    // Handle OAuth or other runtime errors
    Log::error('FedEx API error: ' . $e->getMessage());
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [smart-dato](https://github.com/smart-dato)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
