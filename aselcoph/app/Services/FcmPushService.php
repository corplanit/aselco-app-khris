<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Firebase Cloud Messaging HTTP v1 sender using a service-account JSON file.
 */
class FcmPushService
{
    public function isConfigured(): bool
    {
        $projectId = trim((string) config('services.firebase.project_id', ''));
        $credentials = $this->credentialsPath();

        return $projectId !== '' && $credentials !== null && is_readable($credentials);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if (! $this->isConfigured()) {
            Log::info('fcm.skip_unconfigured', ['token_suffix' => substr($token, -8)]);

            return false;
        }

        try {
            $accessToken = $this->accessToken();
            $projectId = config('services.firebase.project_id');
            $stringData = [];
            foreach ($data as $key => $value) {
                $stringData[(string) $key] = is_scalar($value) || $value === null
                    ? (string) $value
                    : json_encode($value);
            }

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(15)
                ->post(
                    "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                    [
                        'message' => [
                            'token' => $token,
                            'notification' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'data' => $stringData,
                            'android' => [
                                'priority' => 'high',
                            ],
                            'apns' => [
                                'payload' => [
                                    'aps' => [
                                        'sound' => 'default',
                                    ],
                                ],
                            ],
                        ],
                    ]
                );

            if ($response->successful()) {
                return true;
            }

            $status = $response->status();
            Log::warning('fcm.send_failed', [
                'status' => $status,
                'body' => $response->json() ?? $response->body(),
            ]);

            // Drop invalid / unregistered tokens.
            if (in_array($status, [404, 400], true)) {
                DeviceToken::query()->where('token', $token)->delete();
            }

            return false;
        } catch (Throwable $e) {
            Log::warning('fcm.send_exception', ['message' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * @param  iterable<int, string>  $tokens
     * @param  array<string, mixed>  $data
     */
    public function sendToTokens(iterable $tokens, string $title, string $body, array $data = []): int
    {
        $sent = 0;
        foreach ($tokens as $token) {
            $token = trim((string) $token);
            if ($token === '') {
                continue;
            }
            if ($this->sendToToken($token, $title, $body, $data)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function credentialsPath(): ?string
    {
        $path = trim((string) config('services.firebase.credentials', ''));
        if ($path === '') {
            return null;
        }
        if (! str_starts_with($path, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $path)) {
            $path = base_path($path);
        }

        return $path;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadCredentials(): array
    {
        $path = $this->credentialsPath();
        if ($path === null) {
            throw new \RuntimeException('Firebase credentials path is not configured.');
        }

        $json = json_decode((string) file_get_contents($path), true);
        if (! is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            throw new \RuntimeException('Firebase credentials JSON is invalid.');
        }

        return $json;
    }

    private function accessToken(): string
    {
        return Cache::remember('fcm_access_token', 3300, function () {
            $credentials = $this->loadCredentials();
            $now = time();
            $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claim = $this->base64UrlEncode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $unsigned = $header.'.'.$claim;
            $key = openssl_pkey_get_private($credentials['private_key']);
            if ($key === false) {
                throw new \RuntimeException('Unable to parse Firebase private key.');
            }
            $signature = '';
            if (! openssl_sign($unsigned, $signature, $key, OPENSSL_ALGO_SHA256)) {
                throw new \RuntimeException('Unable to sign Firebase JWT.');
            }
            $jwt = $unsigned.'.'.$this->base64UrlEncode($signature);

            $response = Http::asForm()
                ->timeout(15)
                ->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

            if (! $response->successful() || empty($response->json('access_token'))) {
                throw new \RuntimeException('Unable to obtain Firebase access token.');
            }

            return (string) $response->json('access_token');
        });
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
