# Payment Logging Quick Reference

## Location
All payment logs are stored in: `storage/logs/payment/`

## Log File Format
- Files are named: `payment_YYYY-MM-DD.log`
- Each log file covers one calendar day
- Multiple log entries per file (one entry per transaction/event)

## Log Entry Structure

Each log entry is JSON formatted with the following fields:

```json
{
  "timestamp": "ISO 8601 with microseconds",
  "date": "YYYY-MM-DD",
  "correlation_id": "unique request ID",
  "gateway": "Againgency",
  "type": "create_payment_url|api_request|api_response|transaction_state_change",
  "request": { /* sanitized request payload */ },
  "response": { /* sanitized response payload */ },
  "error": "error message if any",
  "execution_time_ms": "milliseconds taken"
}
```

## Accessing Logs Programmatically

### In PHP/Laravel
```php
use App\Services\PaymentLogger;

// Get logs for a specific date
$logContent = PaymentLogger::getLogsForDate('2026-05-25');

// Get list of dates that have logs
$availableDates = PaymentLogger::getAvailableLogDates();

// Clean logs older than 30 days (useful in scheduled tasks)
$deletedCount = PaymentLogger::clearOldLogs(30);
```

### In Artisan Console
```bash
# View log for today
cat storage/logs/payment/payment_$(date +%Y-%m-%d).log

# View log for specific date
cat storage/logs/payment/payment_2026-05-25.log

# Search for specific transaction
grep "correlation_id" storage/logs/payment/payment_2026-05-25.log

# Get payment count for today
grep -c '"event_type"' storage/logs/payment/payment_$(date +%Y-%m-%d).log
```

## Sensitive Data Handling

The logger automatically sanitizes:
- API Keys (shows last 4 chars): `***a1b2`
- Authorization headers: `***REDACTED***`
- Callback secrets: `***REDACTED***`
- Card numbers and CVV: `***REDACTED***`
- Passwords and tokens: `***REDACTED***`

This ensures log files are safe to share for debugging without exposing credentials.

## Log Entry Types

### Payment Flow (Complete Transaction)
```json
{
  "event_type": "PAYMENT_FLOW",
  "type": "create_payment_url",
  "request": { /* full booking payload */ },
  "response": { /* payment API response */ },
  "execution_time_ms": 234.5
}
```

### API Request
```json
{
  "event_type": "API_REQUEST",
  "endpoint": "/orders",
  "method": "POST",
  "headers": { /* sanitized */ }
}
```

### API Response
```json
{
  "event_type": "API_RESPONSE",
  "status_code": 200,
  "headers": { /* response headers */ },
  "body": { /* response payload */ }
}
```

### Transaction State Change
```json
{
  "event_type": "TRANSACTION_STATE_CHANGE",
  "transaction_ref": "aga_uuid",
  "from_state": "initialized",
  "to_state": "pending",
  "metadata": { /* additional info */ }
}
```

## Troubleshooting with Logs

### Find Failed Payments
```bash
grep '"error"' storage/logs/payment/payment_*.log
```

### Find Slow Transactions (>1000ms)
```bash
grep -E '"execution_time_ms": ([1-9][0-9]{3}|[0-9]{4,})' storage/logs/payment/payment_*.log
```

### Track Specific Payment
```bash
# Replace with actual transaction_ref
grep "aga_your-uuid-here" storage/logs/payment/payment_*.log
```

### Check Today's Payment Volume
```bash
DATE=$(date +%Y-%m-%d)
echo "Total requests: $(grep -c 'correlation_id' storage/logs/payment/payment_$DATE.log)"
echo "Failed: $(grep -c '"error":' storage/logs/payment/payment_$DATE.log)"
echo "Successful: $(grep -c '"error": null' storage/logs/payment/payment_$DATE.log)"
```

## Log Retention Policy

By default, logs are kept indefinitely. To implement auto-cleanup:

### Option 1: In a Scheduled Task
```php
// In your scheduled task (e.g., daily)
use App\Services\PaymentLogger;

PaymentLogger::clearOldLogs(30); // Keep only last 30 days
```

### Option 2: Add to `app/Console/Kernel.php`
```php
$schedule->call(function () {
    PaymentLogger::clearOldLogs(30);
})->daily();
```

## Monitoring

### Create a Payment Health Dashboard
```php
// Get stats for today
$date = today()->format('Y-m-d');
$logs = PaymentLogger::getLogsForDate($date);

$lines = explode("\n", $logs);
$totalTransactions = 0;
$failedTransactions = 0;
$totalAmount = 0;

foreach ($lines as $line) {
    if (empty(trim($line))) continue;
    $entry = json_decode($line, true);
    if ($entry && $entry['type'] === 'create_payment_url') {
        $totalTransactions++;
        if ($entry['error']) {
            $failedTransactions++;
        }
    }
}

echo "Total: $totalTransactions, Failed: $failedTransactions";
```

## Tips

1. **Correlation IDs**: Each payment generates a unique UUID for tracking across logs
2. **Timestamp Precision**: Includes microseconds for precise timing analysis
3. **Async Debugging**: Use correlation_id to track requests across different log files
4. **Error Tracking**: All exceptions are logged with full error details
5. **Performance Monitoring**: Execution time helps identify bottlenecks

## Support

For issues with payment processing, always reference the correlation_id found in logs.
Share the relevant log entries (with API keys redacted) with the support team for debugging.
