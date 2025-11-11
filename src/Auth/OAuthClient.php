<?php

namespace SmartDato\FedEx\Auth;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OAuthClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $clientId,
        protected string $clientSecret,
        protected int $tokenCacheTtl = 3500,
        protected string $tokenCacheKey = 'fedex_oauth_token'
    ) {}

    /**
     * Get a valid access token (from cache or by requesting a new one)
     *
     * @throws ConnectionException
     */
    public function getAccessToken(): string
    {
        // Try to get token from cache
        $token = Cache::get($this->tokenCacheKey);

        if ($token) {
            return $token;
        }

        // Request new token
        return $this->requestNewToken();
    }

    /**
     * Request a new OAuth token from FedEx
     *
     * @throws ConnectionException
     */
    protected function requestNewToken(): string
    {
        $response = Http::asForm()
            ->baseUrl($this->baseUrl)
            ->post('/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Failed to obtain OAuth token: '.$response->body()
            );
        }

        $data = $response->json();

        if (! isset($data['access_token'])) {
            throw new RuntimeException('OAuth response missing access_token');
        }

        $accessToken = $data['access_token'];

        // Cache the token
        Cache::put($this->tokenCacheKey, $accessToken, $this->tokenCacheTtl);

        return $accessToken;
    }

    /**
     * Force refresh the token (invalidate cache and request new token)
     *
     * @throws ConnectionException
     */
    public function refreshToken(): string
    {
        Cache::forget($this->tokenCacheKey);

        return $this->requestNewToken();
    }

    /**
     * Get the authorization header value
     *
     * @throws ConnectionException
     */
    public function getAuthorizationHeader(): string
    {
        return 'Bearer '.$this->getAccessToken();
    }

    /**
     * Clear the cached token
     */
    public function clearToken(): void
    {
        Cache::forget($this->tokenCacheKey);
    }
}
