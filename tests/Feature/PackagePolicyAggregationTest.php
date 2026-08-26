<?php

namespace Tests\Feature;

use ReflectionMethod;
use Tests\TestCase;

class PackagePolicyAggregationTest extends TestCase
{
    public function test_payment_before_deadline_uses_highest_percentage_across_operators()
    {
        $controller = new \App\Http\Controllers\Admin\PackageController();
        $method = new ReflectionMethod($controller, 'selectDeadlinePreference');
        $method->setAccessible(true);

        $result = $method->invoke($controller, [
            '20% Payment',
            '100% Payment',
        ], ['100% Payment', '50% Payment', '20% Payment', '0% Payment'], 'payment');

        $this->assertSame('100% Payment', $result);
    }
}
