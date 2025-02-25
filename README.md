# This is my package fedex-sdk

[![Latest Version on Packagist](https://img.shields.io/packagist/v/smart-dato/fedex-sdk.svg?style=flat-square)](https://packagist.org/packages/smart-dato/fedex-sdk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/smart-dato/fedex-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/smart-dato/fedex-sdk/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/smart-dato/fedex-sdk/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/smart-dato/fedex-sdk/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/smart-dato/fedex-sdk.svg?style=flat-square)](https://packagist.org/packages/smart-dato/fedex-sdk)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require smart-dato/fedex-sdk
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="fedex-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$fedex = new SmartDato\FedEx(
    $baseUrl,
    $authorization,
    $labelResponseOptions,
    $accountNumber,
    $contentType
);

$payload = new \SmartDato\FedEx\Payloads\ShipmentPayload(/*...*/);

$shipment = $fedex->createShipment($payload);
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
