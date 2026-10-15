<?php

namespace Tests\Unit;

use App\Http\Controllers\Frontend\BookingController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class CheckoutDateOfBirthValidationTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_checkout_date_of_birth_must_be_strictly_in_the_past(): void
    {
        Carbon::setTestNow('2026-10-15 12:00:00');
        $controller = (new \ReflectionClass(BookingController::class))->newInstanceWithoutConstructor();
        $rulesMethod = new ReflectionMethod(BookingController::class, 'dateOfBirthRules');
        $rulesMethod->setAccessible(true);
        $rules = $rulesMethod->invoke($controller);

        foreach (['2026-10-14', '1990-02-10'] as $pastDate) {
            $validator = Validator::make(['dob' => $pastDate], ['dob' => $rules]);
            $this->assertFalse($validator->fails(), "Expected {$pastDate} to be accepted.");
        }

        foreach (['2026-10-15', '2026-10-16'] as $todayOrFutureDate) {
            $validator = Validator::make(['dob' => $todayOrFutureDate], ['dob' => $rules]);
            $this->assertTrue($validator->fails(), "Expected {$todayOrFutureDate} to be rejected.");
        }

        $nestedValidator = Validator::make([
            'guests' => [
                'cart-item' => [
                    ['dob' => '2026-10-16'],
                ],
            ],
        ], [
            'guests.*.*.dob' => $rules,
        ]);
        $this->assertTrue($nestedValidator->fails());

        $requiredRules = $rulesMethod->invoke($controller, true);
        $requiredValidator = Validator::make([], ['dob' => $requiredRules]);
        $this->assertTrue($requiredValidator->fails());
    }
}
