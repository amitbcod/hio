<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PaymentLogger
{
    protected static $logsPath = 'storage/logs/payment';

    /**
     * Log payment request and response
     */
    public static function logPaymentFlow(
        string $gateway,
        string $type,
        string $correlationId,
        array $request,
        array $response,
        ?string $error = null,
        ?float $executionTime = null
    ): void {
        try {
            self::ensurePaymentLogsDirectory();

            $logFile = self::getLogFilePath();
            $timestamp = now()->format('Y-m-d H:i:s.u');
            $date = now()->toDateString();

            $logEntry = [
                'timestamp' => $timestamp,
                'date' => $date,
                'correlation_id' => $correlationId,
                'gateway' => $gateway,
                'type' => $type,
                'request' => self::sanitizeData($request),
                'response' => self::sanitizeData($response),
                'error' => $error,
                'execution_time_ms' => $executionTime ? round($executionTime * 1000, 2) : null,
            ];

            $logContent = json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n" . str_repeat('=', 80) . "\n";

            File::append($logFile, $logContent);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to write payment log', [
                'error' => $e->getMessage(),
                'gateway' => $gateway,
                'correlation_id' => $correlationId,
            ]);
        }
    }

    /**
     * Log API request details
     */
    public static function logApiRequest(
        string $correlationId,
        string $endpoint,
        string $method,
        array $headers,
        ?array $payload = null
    ): void {
        try {
            self::ensurePaymentLogsDirectory();

            $logFile = self::getLogFilePath();
            $timestamp = now()->format('Y-m-d H:i:s.u');

            $entry = [
                'timestamp' => $timestamp,
                'event_type' => 'API_REQUEST',
                'correlation_id' => $correlationId,
                'endpoint' => $endpoint,
                'method' => $method,
                'headers' => self::sanitizeHeaders($headers),
                'payload' => self::sanitizeData($payload),
            ];

            $logContent = json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
            File::append($logFile, $logContent);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to write API request log', [
                'error' => $e->getMessage(),
                'correlation_id' => $correlationId,
            ]);
        }
    }

    /**
     * Log API response details
     */
    public static function logApiResponse(
        string $correlationId,
        int $statusCode,
        array $responseHeaders,
        ?array $responseBody = null,
        ?float $executionTime = null
    ): void {
        try {
            self::ensurePaymentLogsDirectory();

            $logFile = self::getLogFilePath();
            $timestamp = now()->format('Y-m-d H:i:s.u');

            $entry = [
                'timestamp' => $timestamp,
                'event_type' => 'API_RESPONSE',
                'correlation_id' => $correlationId,
                'status_code' => $statusCode,
                'headers' => self::sanitizeHeaders($responseHeaders),
                'body' => self::sanitizeData($responseBody),
                'execution_time_ms' => $executionTime ? round($executionTime * 1000, 2) : null,
            ];

            $logContent = json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n" . str_repeat('-', 80) . "\n";
            File::append($logFile, $logContent);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to write API response log', [
                'error' => $e->getMessage(),
                'correlation_id' => $correlationId,
            ]);
        }
    }

    /**
     * Log payment transaction state change
     */
    public static function logTransactionState(
        string $correlationId,
        string $transactionRef,
        string $fromState,
        string $toState,
        array $metadata = []
    ): void {
        try {
            self::ensurePaymentLogsDirectory();

            $logFile = self::getLogFilePath();
            $timestamp = now()->format('Y-m-d H:i:s.u');

            $entry = [
                'timestamp' => $timestamp,
                'event_type' => 'TRANSACTION_STATE_CHANGE',
                'correlation_id' => $correlationId,
                'transaction_ref' => $transactionRef,
                'from_state' => $fromState,
                'to_state' => $toState,
                'metadata' => $metadata,
            ];

            $logContent = json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
            File::append($logFile, $logContent);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to write transaction state log', [
                'error' => $e->getMessage(),
                'correlation_id' => $correlationId,
            ]);
        }
    }

    /**
     * Get log file path for today
     */
    protected static function getLogFilePath(): string
    {
        $date = now()->format('Y-m-d');
        return base_path(self::$logsPath . "/payment_{$date}.log");
    }

    /**
     * Ensure payment logs directory exists
     */
    protected static function ensurePaymentLogsDirectory(): void
    {
        $path = base_path(self::$logsPath);
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
        }
    }

    /**
     * Sanitize sensitive data from logs
     */
    protected static function sanitizeData(?array $data): ?array
    {
        if (!is_array($data)) {
            return $data;
        }

        $sanitized = $data;
        $sensitiveKeys = [
            'api_key', 'X-API-KEY', 'authorization', 'token', 'password',
            'card_number', 'cvv', 'ssn', 'secret', 'callback_secret',
            'X-Callback-Secret'
        ];

        foreach ($sensitiveKeys as $key) {
            if (isset($sanitized[$key])) {
                if (is_string($sanitized[$key]) && strlen($sanitized[$key]) > 4) {
                    $sanitized[$key] = '***' . substr($sanitized[$key], -4);
                } else {
                    $sanitized[$key] = '***REDACTED***';
                }
            }
        }

        // Recursively sanitize nested arrays
        foreach ($sanitized as $key => $value) {
            if (is_array($value) && !is_numeric($key)) {
                $sanitized[$key] = self::sanitizeData($value);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize headers for logging
     */
    protected static function sanitizeHeaders(?array $headers): ?array
    {
        if (!is_array($headers)) {
            return $headers;
        }

        $sanitized = [];
        $sensitiveHeaderKeys = ['authorization', 'x-api-key', 'x-callback-secret', 'cookie'];

        foreach ($headers as $key => $value) {
            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $sensitiveHeaderKeys)) {
                if (is_string($value) && strlen($value) > 4) {
                    $sanitized[$key] = '***' . substr($value, -4);
                } else {
                    $sanitized[$key] = '***REDACTED***';
                }
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Get logs for a specific date
     */
    public static function getLogsForDate(string $date): ?string
    {
        try {
            $logFile = base_path(self::$logsPath . "/payment_{$date}.log");
            if (File::exists($logFile)) {
                return File::get($logFile);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to retrieve payment logs', [
                'date' => $date,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Get available log dates
     */
    public static function getAvailableLogDates(): array
    {
        try {
            $path = base_path(self::$logsPath);
            if (!File::isDirectory($path)) {
                return [];
            }

            $files = File::files($path);
            $dates = [];

            foreach ($files as $file) {
                $filename = $file->getBasename();
                if (preg_match('/payment_(\d{4}-\d{2}-\d{2})\.log/', $filename, $matches)) {
                    $dates[] = $matches[1];
                }
            }

            return array_unique($dates);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to retrieve available log dates', [
                'error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Clear old payment logs (older than specified days)
     */
    public static function clearOldLogs(int $daysToKeep = 30): int
    {
        try {
            $path = base_path(self::$logsPath);
            if (!File::isDirectory($path)) {
                return 0;
            }

            $files = File::files($path);
            $deletedCount = 0;
            $cutoffDate = now()->subDays($daysToKeep);

            foreach ($files as $file) {
                $filename = $file->getBasename();
                if (preg_match('/payment_(\d{4}-\d{2}-\d{2})\.log/', $filename, $matches)) {
                    $fileDate = \Carbon\Carbon::parse($matches[1]);
                    if ($fileDate->isBefore($cutoffDate)) {
                        File::delete($file->getPathname());
                        $deletedCount++;
                    }
                }
            }

            return $deletedCount;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to clear old payment logs', [
                'error' => $e->getMessage(),
            ]);
        }

        return 0;
    }
}
