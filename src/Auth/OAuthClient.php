<?php

namespace SmartDato\FedEx\Auth;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OAuthClient
{
    /**
     * Seconds the token lock is held before auto-expiring (guards against a
     * worker that crashes without releasing it).
     */
    private const LOCK_TTL_SECONDS = 15;

    /**
     * Seconds to wait for the token lock before falling back to a direct fetch.
     */
    private const LOCK_WAIT_SECONDS = 10;

    /**
     * The token this client last handed out, used to detect whether a peer has
     * already refreshed the cached token.
     */
    private ?string $issuedToken = null;

    public function __construct(
        protected string $baseUrl,
        protected string $clientId,
        protected string $clientSecret,
        protected int $tokenCacheTtl = 3500,
        protected string $tokenCacheKey = 'fedex_oauth_token'
    ) {}

    /**
     * Get a valid access token (from cache or by requesting a new one).
     *
     * @throws ConnectionException
     */
    public function getAccessToken(): string
    {
        $token = Cache::get($this->tokenCacheKey);

        if ($token) {
            return $this->issuedToken = $token;
        }

        return $this->withTokenLock(
            whenLocked: fn (): string => Cache::get($this->tokenCacheKey) ?: $this->requestNewToken(),
            fallback: fn (): string => $this->requestNewToken(),
        );
    }

    /**
     * Force refresh the token (invalidate cache and request new token).
     *
     * @throws ConnectionException
     */
    public function refreshToken(): string
    {
        return $this->withTokenLock(
            whenLocked: function (): string {
                $cached = Cache::get($this->tokenCacheKey);

                if ($cached && $cached !== $this->issuedToken) {
                    return $cached;
                }

                Cache::forget($this->tokenCacheKey);

                return $this->requestNewToken();
            },
            fallback: function (): string {
                Cache::forget($this->tokenCacheKey);

                return $this->requestNewToken();
            },
        );
    }

    /**
     * Get the authorization header value.
     *
     * @throws ConnectionException
     */
    public function getAuthorizationHeader(): string
    {
        return 'Bearer '.$this->getAccessToken();
    }

    /**
     * Clear the cached token.
     */
    public function clearToken(): void
    {
        Cache::forget($this->tokenCacheKey);
    }

    /**
     * Request a new OAuth token from FedEx and cache it.
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

        Cache::put($this->tokenCacheKey, $accessToken, $this->tokenCacheTtl);

        return $accessToken;
    }

    /**
     * Serialise token generation across concurrent workers.
     *
     * FedEx throttles the OAuth token endpoint per public IP (burst 3/s over 5s,
     * average 1/s over 2min) and penalises a violating IP with a 10 minute
     * 403 FORBIDDEN.ERROR. Without coordination every worker that misses the token
     * cache at the same instant requests its own token, which trips that limit. An
     * atomic lock lets a single worker hit the endpoint while the rest wait and
     * reuse the token it caches.
     *
     * @param  callable(): string  $whenLocked  runs once the lock is held
     * @param  callable(): string  $fallback  runs if the lock cannot be acquired in time
     *
     * @throws ConnectionException
     */
    private function withTokenLock(callable $whenLocked, callable $fallback): string
    {
        $lock = Cache::lock("{$this->tokenCacheKey}:lock", self::LOCK_TTL_SECONDS);

        try {
            $lock->block(self::LOCK_WAIT_SECONDS);
        } catch (LockTimeoutException) {
            return $this->issuedToken = $fallback();
        }

        try {
            return $this->issuedToken = $whenLocked();
        } finally {
            $lock->release();
        }
    }
}
