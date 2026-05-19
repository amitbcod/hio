<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AgaingencyPaymentService
{
    public static function createPaymentUrl(string $orderId, string $transactionRef, string $customerEmail, string $customerName, float $amount, string $currency, string $successUrl, string $failureUrl, string $callbackUrl, ?string $startDate = null, ?string $endDate = null): string
    {
        return self::createPaymentSession($orderId, $transactionRef, $customerEmail, $customerName, $amount, $currency, $successUrl, $failureUrl, $callbackUrl, $startDate, $endDate)['payment_url'];
    }

    public static function createPaymentSession(string $orderId, string $transactionRef, string $customerEmail, string $customerName, float $amount, string $currency, string $successUrl, string $failureUrl, string $callbackUrl, ?string $startDate = null, ?string $endDate = null): array
    {
        $startTime = microtime(true);
        $correlationId = Str::uuid();
        $logger = self::getLogger();
        $config = config('app.Againgencypayment');

        $logger->debug('Againgency payment config loaded', [
            'correlation_id' => $correlationId,
            'config_present' => is_array($config),
            'config_cached' => app()->configurationIsCached(),
            'api_key' => self::maskSecret($config['api_key'] ?? null),
            'username' => $config['username'] ?? null,
            'email' => $config['email'] ?? null,
            'env_api_key_present' => !empty(env('AGAINCENCY_PAYMENT_API_KEY')),
            'env_username_present' => !empty(env('AGAINCENCY_PAYMENT_USERNAME')),
            'env_email_present' => !empty(env('AGAINCENCY_PAYMENT_EMAIL')),
            'logging_channel_defined' => config('logging.channels.againgency') !== null,
            'default_log_channel' => config('logging.default'),
        ]);

        // Log initial request parameters
        $logger->info('Againgency Payment URL Request Initiated', [
            'correlation_id' => $correlationId,
            'transaction_ref' => $transactionRef,
            'customer_email' => $customerEmail,
            'customer_name' => $customerName,
            'amount' => $amount,
            'currency' => $currency,
            'timestamp' => now()->toIso8601String(),
        ]);

        if (empty($config['api_key']) && (empty($config['username']) || empty($config['email']))) {
            $logger->error('Againgency Payment Config Missing', [
                'correlation_id' => $correlationId,
                'error' => 'Credentials not configured',
                'api_key_present' => !empty($config['api_key']),
                'username_present' => !empty($config['username']),
                'email_present' => !empty($config['email']),
                'env_api_key_present' => !empty(env('AGAINCENCY_PAYMENT_API_KEY')),
                'env_username_present' => !empty(env('AGAINCENCY_PAYMENT_USERNAME')),
                'env_email_present' => !empty(env('AGAINCENCY_PAYMENT_EMAIL')),
            ]);
            throw new \RuntimeException('Againgency payment credentials are not configured.');
        }

        $endpointBase = rtrim(self::getApiBaseUrl(), '/');
        $useNewApi = !empty($config['api_key']);

        if ($useNewApi) {
            $endpoint = $endpointBase . '/orders';
            $position = [
                'name' => 'Booking ' . $orderId,
                'type' => 'OTHER',
                'quantity' => 1,
                'price' => number_format($amount, 2, '.', ''),
                'amount' => number_format($amount, 2, '.', ''),
            ];

            if (!empty($startDate)) {
                $position['start_date'] = $startDate;
            }
            if (!empty($endDate)) {
                $position['end_date'] = $endDate;
            }

            $payload = [
                'external_id' => $orderId,
                'currency_code' => strtoupper($currency),
                'internal_description' => 'Booking ' . $orderId,
                'positions' => [$position],
                'customer' => [
                    'first_name' => self::extractFirstName($customerName),
                    'last_name' => self::extractLastName($customerName),
                    'email' => $customerEmail,
                    'language' => 'en',
                ],
                'total' => [
                    'sum' => number_format($amount, 2, '.', ''),
                ],
                'customer_notifications' => [
                    'email' => false,
                ],
                'payments' => [
                    [
                        'type' => 'LINK',
                        'payment_methods' => $config['payment_methods'] ?? ['CARD'],
                        'amount' => number_format($amount, 2, '.', ''),
                        'skip_pp' => false,
                        'auth_method' => strtoupper($config['auth_method'] ?? 'AUTH'),
                        'customer_email' => $customerEmail,
                        'callback_url' => $callbackUrl,
                        'success_url' => $successUrl,
                        'fail_url' => $failureUrl,
                    ],
                ],
            ];
        } else {
            $endpoint = $endpointBase . '/payment/create';
            $payload = [
                'username' => $config['username'],
                'email' => $config['email'],
                'transaction_ref' => $transactionRef,
                'order_id' => $orderId,
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => strtoupper($currency),
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'success_url' => $successUrl,
                'failure_url' => $failureUrl,
                'callback_url' => $callbackUrl,
            ];
        }

        $logger->debug('Againgency Payment Request Payload', [
            'correlation_id' => $correlationId,
            'endpoint' => $endpoint,
            'method' => 'POST',
            'api_version' => $useNewApi ? 'v2 (API Key)' : 'v1 (Legacy)',
            'payload' => $payload,
            'payments' => $payload['payments'] ?? null,
        ]);

        $request = Http::timeout(30)
            ->acceptJson();

        if (!empty($config['api_key'])) {
            $request = $request->withHeaders(['X-API-KEY' => $config['api_key']]);
        }

        if (!empty($config['callback_secret'])) {
            $request = $request->withHeaders(['X-Callback-Secret' => $config['callback_secret']]);
        }

        $headers = [
            'Accept' => 'application/json',
            'Timeout' => '30s',
            'X-API-KEY' => !empty($config['api_key']) ? '***' . substr($config['api_key'], -4) : 'N/A',
            'X-Callback-Secret' => !empty($config['callback_secret']) ? '***' : 'N/A',
        ];
        $logger->debug('Againgency Request Headers', [
            'correlation_id' => $correlationId,
            'headers' => $headers,
        ]);

        try {
            $response = $request->post($endpoint, $payload);
        } catch (\Exception $e) {
            $logger->error('Againgency Payment Request Failed - Exception', [
                'correlation_id' => $correlationId,
                'endpoint' => $endpoint,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'exception_class' => get_class($e),
                'elapsed_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
            ]);
            throw $e;
        }

        $logger->info('Againgency Payment Response Received', [
            'correlation_id' => $correlationId,
            'endpoint' => $endpoint,
            'status_code' => $response->status(),
            'successful' => $response->successful(),
            'response_headers' => [
                'Content-Type' => $response->header('Content-Type'),
                'Content-Length' => $response->header('Content-Length'),
                'Server' => $response->header('Server'),
                'Cache-Control' => $response->header('Cache-Control'),
            ],
            'elapsed_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
        ]);

        if (!$response->successful()) {
            $errorBody = $response->body();
            $logger->error('Againgency Payment Request Failed - HTTP Error', [
                'correlation_id' => $correlationId,
                'endpoint' => $endpoint,
                'status_code' => $response->status(),
                'response_body' => $errorBody,
                'parsed_json' => json_decode($errorBody, true),
                'elapsed_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
            ]);
            throw new \RuntimeException('Againgency payment service returned an error: ' . $errorBody);
        }

        $body = $response->json();

        $logger->debug('Againgency Payment Response Body', [
            'correlation_id' => $correlationId,
            'response' => $body,
        ]);

        $paymentUrl = null;

        if (!empty($body['payload']['payments'][0]['link'])) {
            $paymentUrl = $body['payload']['payments'][0]['link'];
        } elseif (!empty($body['payments'][0]['link'])) {
            $paymentUrl = $body['payments'][0]['link'];
        } elseif (!empty($body['payment_url'])) {
            $paymentUrl = $body['payment_url'];
        } elseif (!empty($body['data']['payment_url'])) {
            $paymentUrl = $body['data']['payment_url'];
        } elseif (!empty($body['data']['redirect_url'])) {
            $paymentUrl = $body['data']['redirect_url'];
        } elseif (!empty($body['payload']['link'])) {
            $paymentUrl = $body['payload']['link'];
        } elseif (!empty($body['link'])) {
            $paymentUrl = $body['link'];
        }

        if ($paymentUrl === null) {
            $logger->error('Againgency Payment Response - No Redirect URL Found', [
                'correlation_id' => $correlationId,
                'response_body' => $body,
                'available_keys' => array_keys($body),
                'elapsed_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
            ]);
            throw new \RuntimeException('Againgency payment response did not contain a redirect URL.');
        }

        $paymentId = self::extractPaymentId($body);

        $logger->info('Againgency Payment URL Generated Successfully', [
            'correlation_id' => $correlationId,
            'transaction_ref' => $transactionRef,
            'payment_url' => $paymentUrl,
            'payment_id' => $paymentId,
            'elapsed_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
        ]);

        return [
            'payment_url' => $paymentUrl,
            'payment_id' => $paymentId,
            'response' => $body,
        ];
    }

    public static function resolveCallbackStatus(string $status): string
    {
        $status = strtolower(trim($status));

        return match ($status) {
            'paid', 'success', 'successful', 'completed' => 'paid',
            'refunded' => 'refunded',
            'failed', 'declined', 'rejected', 'cancelled' => 'failed',
            default => 'pending',
        };
    }

    public static function getPaymentCallbacks(string $paymentId): array
    {
        $startTime = microtime(true);
        $correlationId = Str::uuid();
        $logger = self::getLogger();
        $config = config('app.Againgencypayment');

        $logger->debug('Againgency callback config loaded', [
            'correlation_id' => $correlationId,
            'config_present' => is_array($config),
            'config_cached' => app()->configurationIsCached(),
            'api_key' => self::maskSecret($config['api_key'] ?? null),
            'logging_channel_defined' => config('logging.channels.againgency') !== null,
            'default_log_channel' => config('logging.default'),
        ]);

        $logger->info('Againgency Get Callbacks Request Initiated', [
            'correlation_id' => $correlationId,
            'payment_id' => $paymentId,
            'timestamp' => now()->toIso8601String(),
        ]);

        if (empty($config['api_key'])) {
            $logger->error('Againgency Get Callbacks - API Key Missing', [
                'correlation_id' => $correlationId,
                'payment_id' => $paymentId,
            ]);
            throw new \RuntimeException('Againgency API key is not configured.');
        }

        $endpoint = rtrim(self::getApiBaseUrl(), '/') . '/payments/' . $paymentId . '/callbacks';

        $logger->debug('Againgency Get Callbacks Request', [
            'correlation_id' => $correlationId,
            'endpoint' => $endpoint,
            'method' => 'GET',
            'headers' => [
                'Accept' => 'application/json',
                'X-API-KEY' => '***' . substr($config['api_key'], -4),
            ],
        ]);

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->withHeaders(['X-API-KEY' => $config['api_key']])
                ->get($endpoint);
        } catch (\Exception $e) {
            $logger->error('Againgency Get Callbacks Request Failed - Exception', [
                'correlation_id' => $correlationId,
                'endpoint' => $endpoint,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'exception_class' => get_class($e),
                'elapsed_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
            ]);
            throw $e;
        }

        $logger->info('Againgency Get Callbacks Response Received', [
            'correlation_id' => $correlationId,
            'endpoint' => $endpoint,
            'status_code' => $response->status(),
            'successful' => $response->successful(),
            'elapsed_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
        ]);

        if (!$response->successful()) {
            $errorBody = $response->body();
            $logger->error('Againgency Get Callbacks Request Failed - HTTP Error', [
                'correlation_id' => $correlationId,
                'endpoint' => $endpoint,
                'status_code' => $response->status(),
                'response_body' => $errorBody,
                'elapsed_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
            ]);
            throw new \RuntimeException('Failed to retrieve payment callbacks: ' . $errorBody);
        }

        $callbackData = $response->json();

        $logger->debug('Againgency Get Callbacks Response Body', [
            'correlation_id' => $correlationId,
            'payment_id' => $paymentId,
            'callbacks_count' => is_array($callbackData) ? count($callbackData) : 0,
            'response' => $callbackData,
            'elapsed_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
        ]);

        $logger->info('Againgency Get Callbacks Completed Successfully', [
            'correlation_id' => $correlationId,
            'payment_id' => $paymentId,
            'elapsed_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
        ]);

        return $callbackData;
    }

    private static function extractPaymentId(array $body): ?string
    {
        $candidates = [
            data_get($body, 'payload.payments.0.id'),
            data_get($body, 'payload.payments.0.payment_id'),
            data_get($body, 'payments.0.id'),
            data_get($body, 'payments.0.payment_id'),
            data_get($body, 'payment.id'),
            data_get($body, 'data.payment.id'),
            data_get($body, 'payload.payment.id'),
            data_get($body, 'payment_id'),
        ];

        foreach ($candidates as $candidate) {
            if (!empty($candidate)) {
                return (string) $candidate;
            }
        }

        return null;
    }

    private static function extractFirstName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName));

        return $parts[0] ?? $fullName;
    }

    private static function extractLastName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName));

        if (count($parts) <= 1) {
            return '';
        }

        array_shift($parts);

        return implode(' ', $parts);
    }

    private static function getApiBaseUrl(): string
    {
        $config = config('app.Againgencypayment');

        return !empty($config['api_base_url'])
            ? rtrim($config['api_base_url'], '/')
            : 'https://api.againgency.com/api/v1';
    }

    private static function getLogger()
    {
        try {
            if (config('logging.channels.againgency')) {
                return Log::channel('againgency');
            }
        } catch (\Throwable $e) {
            // fallback to default log channel if custom channel is not available
        }

        return Log::channel(config('logging.default', 'stack'));
    }

    private static function maskSecret(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return strlen($value) <= 8 ? str_repeat('*', strlen($value)) : '***' . substr($value, -4);
    }
}
