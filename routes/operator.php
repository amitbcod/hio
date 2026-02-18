<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operator\AuthController;
use App\Http\Controllers\Operator\ProfileController;
use App\Http\Controllers\Operator\RegistrationController;
use App\Http\Controllers\Operator\AccommodationController;

Route::prefix('operator')->name('operator.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:operator,operator_staff')->group(function () {
        Route::get('profile', [ProfileController::class, 'showProfile'])->name('profile');
        // Registration/Profile Steps
        // Step 1 (Set Password) skipped after registration
        Route::get('register/step2-profile', [RegistrationController::class, 'step2Profile'])->name('register.step2');
        Route::post('register/step2-profile', [RegistrationController::class, 'saveStep2Profile']);
        // Step 3 (Legal Compliance) is now handled via modal in step 2 - only POST is available
        // Route::get('register/step3-legal', [RegistrationController::class, 'step3Legal'])->name('register.step3');
        Route::post('register/step3-legal', [RegistrationController::class, 'saveStep3Legal']);
        Route::get('register/step4-system-process', [RegistrationController::class, 'step4SystemProcess'])->name('register.step4');
        Route::post('register/step4-system-process', [RegistrationController::class, 'saveStep4SystemProcess']);
        Route::get('register/step5-collaboration', [RegistrationController::class, 'step5Collaboration'])->name('register.step5');
        Route::post('register/step5-collaboration', [RegistrationController::class, 'saveStep5Collaboration']);

        Route::get('register/step6-users', [RegistrationController::class, 'step6Users'])->name('register.step6');
        Route::post('register/step6-users', [RegistrationController::class, 'saveStep6Users']);
        Route::get('register/step6-users/{user}/edit', [RegistrationController::class, 'editStep6User'])->name('register.step6.user.edit');
        Route::post('register/step6-users/{user}/edit', [RegistrationController::class, 'updateStep6User'])->name('register.step6.user.update');
        Route::post('register/step6-users/{user}/delete', [RegistrationController::class, 'deleteStep6User'])->name('register.step6.user.delete');
        Route::post('register/step6-role-access', [RegistrationController::class, 'saveRoleAccessMapping'])->name('register.step6.role-access.save');
        Route::delete('register/step6-role-access/{mapping}', [RegistrationController::class, 'deleteRoleAccessMapping'])->name('register.step6.role-access.delete');
        Route::get('register/step7-accounting', [RegistrationController::class, 'step7Accounting'])->name('register.step7');
        Route::post('register/step7-accounting', [RegistrationController::class, 'saveStep7Accounting'])->name('register.step7');
        // Payouts additional details (modal save)
        Route::post('register/step7-payouts', [RegistrationController::class, 'savePayoutDetails'])->name('register.step7.payouts.save');

        // Owner management (only for owners)
        Route::get('manage/operators', [\App\Http\Controllers\Operator\ManageOperatorsController::class, 'index'])->name('manage.operators.index');
        Route::post('manage/operators/{id}/status', [\App\Http\Controllers\Operator\ManageOperatorsController::class, 'updateStatus'])->name('manage.operators.update_status');

        // Business role management (owner-only)
        Route::get('roles', [\App\Http\Controllers\Operator\RoleController::class, 'index'])->name('roles.index');
        Route::post('roles', [\App\Http\Controllers\Operator\RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}/edit', [\App\Http\Controllers\Operator\RoleController::class, 'edit'])->name('roles.edit');
        Route::post('roles/{role}', [\App\Http\Controllers\Operator\RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [\App\Http\Controllers\Operator\RoleController::class, 'destroy'])->name('roles.destroy');
        Route::get('roles/{role}/permissions', [\App\Http\Controllers\Operator\RoleController::class, 'permissions'])->name('roles.permissions');
        Route::post('roles/{role}/permissions', [\App\Http\Controllers\Operator\RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

        // Controller verification flows (accept/reject require auth)
        Route::post('register/controller/verify/{token}/accept', [\App\Http\Controllers\Operator\ControllerVerificationController::class, 'accept'])->name('register.controller.verify.accept');
        Route::post('register/controller/verify/{token}/reject', [\App\Http\Controllers\Operator\ControllerVerificationController::class, 'reject'])->name('register.controller.verify.reject');

        Route::get('register/step8-operations', [RegistrationController::class, 'step8Operations'])->name('register.step8');
        Route::post('register/step8-operations', [RegistrationController::class, 'saveStep8Operations'])->name('register.step8');
        Route::get('register/step9-review', [RegistrationController::class, 'step9Review'])->name('register.step9');
        Route::post('register/step9-review', [RegistrationController::class, 'saveStep9Review']);

       Route::post('status-submit', [\App\Http\Controllers\Operator\RegistrationController::class, 'submitForApproval'])->name('status.submit');

        // Accommodation Management Routes
        Route::get('accommodation', [AccommodationController::class, 'index'])->name('accommodation.index');
        Route::get('accommodation/create', [AccommodationController::class, 'create'])->name('accommodation.create');
        Route::post('accommodation', [AccommodationController::class, 'store'])->name('accommodation.store');
        Route::get('accommodation/{id}', [AccommodationController::class, 'show'])->name('accommodation.show');
        Route::get('accommodation/{id}/edit/step1', [AccommodationController::class, 'editStep1'])->name('accommodation.step1.edit');
        Route::put('accommodation/{id}', [AccommodationController::class, 'update'])->name('accommodation.update');
        Route::get('accommodation/{id}/step2-reservation', [AccommodationController::class, 'step2Reservation'])->name('accommodation.step2.show');
        Route::post('accommodation/{id}/step2-reservation', [AccommodationController::class, 'saveStep2'])->name('accommodation.saveStep2');
        Route::get('accommodation/{id}/step3-photos', [AccommodationController::class, 'step3Photos'])->name('accommodation.step3.show');
        Route::post('accommodation/{id}/step3-photos', [AccommodationController::class, 'saveStep3Photos'])->name('accommodation.saveStep3');
        Route::get('accommodation/{id}/step4-compliance', [AccommodationController::class, 'step4Compliance'])->name('accommodation.step4.show');
        Route::post('accommodation/{id}/step4-compliance', [AccommodationController::class, 'saveStep4Compliance'])->name('accommodation.saveStep4');
        Route::get('accommodation/{id}/step5-accounting', [AccommodationController::class, 'step5Accounting'])->name('accommodation.step5.show');
        Route::post('accommodation/{id}/step5-accounting', [AccommodationController::class, 'saveStep5Accounting'])->name('accommodation.saveStep5');
        Route::get('accommodation/{id}/step6-policies-rules', [AccommodationController::class, 'step6PoliciesRules'])->name('accommodation.step6.show');
        Route::post('accommodation/{id}/step6-policies-rules', [AccommodationController::class, 'saveStep6PoliciesRules'])->name('accommodation.saveStep6');
        // Step 7: Rooms & Units
        Route::get('accommodation/{id}/step7-rooms', [AccommodationController::class, 'step7RoomsUnits'])->name('accommodation.step7.show');
        Route::post('accommodation/{id}/step7-rooms', [AccommodationController::class, 'saveRoom'])->name('accommodation.saveStep7');
        Route::get('accommodation/{id}/step7-rooms/{room}/edit', [AccommodationController::class, 'editRoom'])->name('accommodation.step7.room.edit');
        Route::post('accommodation/{id}/step7-rooms/{room}/edit', [AccommodationController::class, 'updateRoom'])->name('accommodation.step7.room.update');
        Route::post('accommodation/{id}/step7-rooms/{room}/delete', [AccommodationController::class, 'deleteRoom'])->name('accommodation.step7.room.delete');
        // Step 8: Rate Plans
        Route::get('accommodation/{id}/step8-rate-plans', [AccommodationController::class, 'step8RatePlans'])->name('accommodation.step8.show');
        Route::post('accommodation/{id}/step8-rate-plans', [AccommodationController::class, 'saveRatePlan'])->name('accommodation.saveStep8');
        Route::get('accommodation/{id}/step8-rate-plans/{plan}/edit', [AccommodationController::class, 'editRatePlan'])->name('accommodation.step8.plan.edit');
        Route::put('accommodation/{id}/step8-rate-plans/{plan}/edit', [AccommodationController::class, 'updateRatePlan'])->name('accommodation.step8.plan.update');
        Route::post('accommodation/{id}/step8-rate-plans/{plan}/delete', [AccommodationController::class, 'deleteRatePlan'])->name('accommodation.step8.plan.delete');
        Route::post('accommodation/{id}/step8-assign-plans', [AccommodationController::class, 'assignPlansToRoom'])->name('accommodation.step8.assignPlans')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
        Route::post('accommodation/{id}/step8-remove-plan', [AccommodationController::class, 'removePlanFromRoom'])->name('accommodation.step8.removePlan')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
        // Step 9: Season and Pricing
        Route::get('accommodation/{id}/step9-season-pricing', [AccommodationController::class, 'step9SeasonPricing'])->name('accommodation.step9.show');
        Route::post('accommodation/{id}/step9-season-pricing', [AccommodationController::class, 'saveSeasonPricing'])->name('accommodation.saveStep9');
        Route::post('accommodation/{id}/step9-set-default-price', [AccommodationController::class, 'setDefaultPrice'])->name('accommodation.step9.setDefaultPrice');
        Route::post('accommodation/{id}/step9-add-season', [AccommodationController::class, 'addSeasonalEntry'])->name('accommodation.step9.addSeason');
        Route::post('accommodation/{id}/step9-delete-season/{entryId}', [AccommodationController::class, 'deleteSeasonalEntry'])->name('accommodation.step9.deleteSeason');
        Route::post('accommodation/{id}/step9-update-season/{entryId}', [AccommodationController::class, 'updateSeasonalEntry'])->name('accommodation.step9.updateSeason');
        Route::get('accommodation/{id}/step9-season-pricing/{pricing}/edit', [AccommodationController::class, 'editSeasonPricing'])->name('accommodation.step9.pricing.edit');
        Route::post('accommodation/{id}/step9-season-pricing/{pricing}/edit', [AccommodationController::class, 'updateSeasonPricing'])->name('accommodation.step9.pricing.update');
        Route::post('accommodation/{id}/step9-season-pricing/{pricing}/delete', [AccommodationController::class, 'deleteSeasonPricing'])->name('accommodation.step9.pricing.delete');

        // Save accommodation fees
        Route::post('accommodation/{id}/fees', [AccommodationController::class, 'saveFees'])->name('accommodation.fees.save');
        Route::get('accommodation/{id}/fees', [AccommodationController::class, 'getFees'])->name('accommodation.fees.get');

        // Additional fees routes (preferred name)
        Route::post('accommodation/{id}/additional-fees', [AccommodationController::class, 'saveFees'])->name('accommodation.additional_fees.save');
        Route::get('accommodation/{id}/additional-fees', [AccommodationController::class, 'getFees'])->name('accommodation.additional_fees.get');

        Route::get('pending-approval', function () {
            return view('operator.registration.pending_approval');
        })->name('pending.approval');
    });

    // Public owner verification routes (show and claim) — not protected by auth middleware
    Route::get('register/controller/verify/{token}', [\App\Http\Controllers\Operator\ControllerVerificationController::class, 'show'])->name('register.controller.verify');
    Route::post('register/controller/verify/{token}/claim', [\App\Http\Controllers\Operator\ControllerVerificationController::class, 'claim'])->name('register.controller.verify.claim');

    // HIO Service Agreement PDF
    Route::get('hio-agreement', [\App\Http\Controllers\Operator\ResponsibilitiesPdfController::class, 'agreement'])->name('hio.agreement');

    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    Route::get('responsibilities-pdf', [\App\Http\Controllers\Operator\ResponsibilitiesPdfController::class, 'download'])->name('responsibilities.pdf');
});
