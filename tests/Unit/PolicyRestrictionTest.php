<?php

namespace Tests\Unit;

use App\Http\Controllers\Operator\AccommodationController;
use App\Http\Controllers\Operator\ActivityController;
use App\Models\Operator;
use PHPUnit\Framework\TestCase;

class PolicyRestrictionTest extends TestCase
{
    public function test_activity_controller_forces_template_policies_for_oto_agreements(): void
    {
        $controller = new ActivityController();
        $operator = new Operator(['agreement_type' => 'OTO']);

        $method = new \ReflectionMethod(ActivityController::class, 'shouldForceTemplatePolicies');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($controller, $operator));
    }

    public function test_accommodation_controller_forces_template_policies_for_full_agreement(): void
    {
        $controller = new AccommodationController();
        $operator = new Operator(['agreement_type' => 'Full agreement']);

        $method = new \ReflectionMethod(AccommodationController::class, 'shouldForceTemplatePolicies');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($controller, $operator));
    }

    public function test_full_service_agreements_force_template_policies(): void
    {
        $controller = new ActivityController();
        $operator = new Operator(['agreement_type' => 'Full Service']);

        $method = new \ReflectionMethod(ActivityController::class, 'shouldForceTemplatePolicies');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($controller, $operator));
    }

    public function test_listing_only_agreements_do_not_force_template_policies(): void
    {
        $controller = new ActivityController();
        $operator = new Operator(['agreement_type' => 'Listing Only']);

        $method = new \ReflectionMethod(ActivityController::class, 'shouldForceTemplatePolicies');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($controller, $operator));
    }
}
