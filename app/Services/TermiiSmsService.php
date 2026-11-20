<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Bulk SMS through Termii, a Nigerian SMS gateway. Optional: when no API key
 * is configured, parent messages fall back to WhatsApp click-to-chat links.
 */
class TermiiSmsService
{
    /**
     * Termii accepts at most this many numbers per bulk request.
     */
    public const BATCH_SIZE = 100;

    protected string $apiKey;

    protected string $senderId;

    protected string $channel;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.termii.api_key');
        $this->senderId = (string) config('services.termii.sender_id');
        $this->channel = (string) config('services.termii.channel', 'generic');
        $this->baseUrl = rtrim((string) config('services.termii.base_url', 'https://api.ng.termii.com'), '/');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->senderId !== '';
    }

    /**
     * @param  array<int, string>  $phoneNumbers  international format without '+', e.g. 2348031234567
     *
     * @throws RuntimeException when Termii rejects a batch
     */
    public function sendBulk(array $phoneNumbers, string $message): void
    {
        foreach (array_chunk($phoneNumbers, self::BATCH_SIZE) as $batch) {
            $response = Http::acceptJson()->post($this->baseUrl.'/api/sms/send/bulk', [
                'api_key' => $this->apiKey,
                'to' => $batch,
                'from' => $this->senderId,
                'sms' => $message,
                'type' => 'plain',
                'channel' => $this->channel,
            ]);

            if ($response->failed()) {
                throw new RuntimeException('SMS gateway error: '.($response->json('message') ?? $response->status()));
            }
        }
    }
}
