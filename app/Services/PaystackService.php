<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaystackService
{
    protected string $secretKey;

    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = (string) config('services.paystack.secret_key');
        $this->baseUrl = rtrim((string) config('services.paystack.payment_url', 'https://api.paystack.co'), '/');
    }

    public function isConfigured(): bool
    {
        return $this->secretKey !== '';
    }

    /**
     * Initialize a transaction. Amount must be in the major currency unit
     * (e.g. Naira); Paystack expects the amount in the smallest unit (kobo).
     */
    public function initialize(string $email, float $amount, string $reference, string $callbackUrl, array $metadata = []): array
    {
        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $email,
                'amount' => (int) round($amount * 100),
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'metadata' => $metadata,
            ]);

        return [
            'ok' => $response->successful() && data_get($response->json(), 'status') === true,
            'body' => $response->json(),
        ];
    }

    public function verify(string $reference): array
    {
        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->get("{$this->baseUrl}/transaction/verify/".rawurlencode($reference));

        return [
            'ok' => $response->successful() && data_get($response->json(), 'status') === true,
            'body' => $response->json(),
        ];
    }
}
