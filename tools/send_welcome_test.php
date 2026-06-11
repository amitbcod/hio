<?php
// Boot Laravel and send a test TravelerWelcomeMail to a target email.

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\TravelerWelcomeMail;
use App\Models\TravelerAccount;

// Create a lightweight TravelerAccount instance (not persisted)
$account = new TravelerAccount();
$account->full_name = 'Test User';
$account->email = 'amit29592@gmail.com';

try {
    Mail::to($account->email)->send(new TravelerWelcomeMail($account));
    echo "Mail send invoked to {$account->email}\n";
} catch (Exception $e) {
    echo "Mail send failed: " . $e->getMessage() . "\n";
}

return 0;
