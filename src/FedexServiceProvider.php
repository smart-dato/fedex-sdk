<?php

namespace SmartDato\FedEx;

use SmartDato\FedEx\Auth\OAuthClient;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FedExServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('fedex')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        // Register OAuth Client
        $this->app->singleton(OAuthClient::class, function ($app) {
            $config = config('fedex');
            $environment = $config['environment'] ?? 'sandbox';
            $baseUrl = $config['base_url'][$environment] ?? $config['base_url']['sandbox'];

            return new OAuthClient(
                baseUrl: $baseUrl,
                clientId: $config['client_id'],
                clientSecret: $config['client_secret'],
                tokenCacheTtl: $config['token_cache_ttl'] ?? 3500,
                tokenCacheKey: $config['token_cache_key'] ?? 'fedex_oauth_token'
            );
        });

        // Register Fedex Service
        $this->app->singleton(Fedex::class, function ($app) {
            return new Fedex(
                oauthClient: $app->make(OAuthClient::class),
                config: config('fedex', [])
            );
        });
    }
}
