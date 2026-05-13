<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AgaingencyPaymentService
{
    public static function createPaymentUrl(string $transactionRef, string $customerEmail, string $customerName, float $amount, string $currency, string $successUrl, string $failureUrl, string $callbackUrl): string
    {
        $config = config('app.Againgencypayment');

        if (empty($config['username']) || empty($config['email'])) {
            throw new \RuntimeException('Againgency payment credentials are not configured.');
        }

        $endpoint = rtrim(self::getApiBaseUrl(), '/') . '/payment/create';
        $payload = [
            'username' => $config['username'],
            'email' => $config['email'],
            'transaction_ref' => $transactionRef,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => strtoupper($currency),
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'success_url' => $successUrl,
            'failure_url' => $failureUrl,
            'callback_url' => $callbackUrl,
        ];

        $request = Http::timeout(30)
            ->acceptJson();

        if (!empty($config['callback_secret'])) {
            $request = $request->withHeaders(['X-Callback-Secret' => $config['callback_secret']]);
        }

        $response = $request->post($endpoint, $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Againgency payment service returned an error: ' . $response->body());
        }

        $body = $response->json();

        if (!empty($body['payment_url'])) {
            return $body['payment_url'];
        }

        if (!empty($body['data']['payment_url'])) {
            return $body['data']['payment_url'];
        }

        if (!empty($body['redirect_url'])) {
            return $body['redirect_url'];
        }

        if (!empty($body['data']['redirect_url'])) {
            return $body['data']['redirect_url'];
        }

        throw new \RuntimeException('Againgency payment response did not contain a redirect URL.');
    }

    public static function resolveCallbackStatus(string $status): string
    {
        $status = strtolower(trim($status));

        return match ($status) {
            'paid', 'success', 'successful', 'completed' => 'paid',
            'refunded' => 'refunded',
            default => 'pending',
        };
    }

    public static function getPaymentCallbacks(string $paymentId): array
    {
        $config = config('app.Againgencypayment');

        if (empty($config['api_key'])) {
            throw new \RuntimeException('Againgency API key is not configured.');
        }

        $endpoint = rtrim(self::getApiBaseUrl(), '/') . '/payments/' . $paymentId . '/callbacks';

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders(['X-API-KEY' => $config['api_key']])
            ->get($endpoint);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to retrieve payment callbacks: ' . $response->body());
        }

        return $response->json();
    }

    private static function getApiBaseUrl(): string
    {
        $config = config('app.Againgencypayment');

        return !empty($config['api_base_url'])
            ? rtrim($config['api_base_url'], '/')
            : 'https://api.againgency.com/api/v1';
    }
}
