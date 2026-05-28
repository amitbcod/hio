<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operator\AuthController;
use App\Http\Controllers\Operator\ProfileController;
use App\Http\Controllers\Operator\RegistrationController;
use App\Http\Controllers\Operator\AccommodationController;
use App\Http\Controllers\Operator\ActivityController;
use App\Http\Controllers\Operator\SharedCartController;

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
        Route::get('accommodation/bookings', [AccommodationController::class, 'bookingList'])->name('accommodation.bookings');
        Route::get('accommodation/bookings/{booking}', [AccommodationController::class, 'bookingDetails'])->name('accommodation.booking.details');
        Route::post('accommodation/bookings/{booking}/status', [AccommodationController::class, 'updateBookingStatus'])->name('accommodation.booking.status');
        Route::get('accommodation/create', [AccommodationController::class, 'create'])->name('accommodation.create');
        Route::post('accommodation', [AccommodationController::class, 'store'])->name('accommodation.store');
        Route::get('accommodation/{id}', [AccommodationController::class, 'show'])->name('accommodation.show');
        Route::get('accommodation/{id}/edit/step1', [AccommodationController::class, 'editStep1'])->name('accommodation.step1.edit');
        Route::put('accommodation/{id}', [AccommodationController::class, 'update'])->name('accommodation.update');
        Route::get('accommodation/{id}/step2-reservation', [AccommodationController::class, 'step2Reservation'])->name('accommodation.step2.show');
        Route::post('accommodation/{id}/step2-reservation', [AccommodationController::class, 'saveStep2'])->name('accommodation.saveStep2');
        Route::get('accommodation/{id}/step3-photos', [AccommodationController::class, 'step3Photos'])->name('accommodation.step3.show');
        Route::post('accommodation/{id}/step3-photos', [AccommodationController::class, 'saveStep3Photos'])->name('accommodation.saveStep3');
        Route::post('accommodation/{id}/media/{mediaId}/delete', [AccommodationController::class, 'deleteMedia'])->name('accommodation.media.delete');
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

        // Step 10: Inventory & Allotment
        Route::get('accommodation/{id}/step10-inventory-allotment', [AccommodationController::class, 'step10InventoryAllotment'])->name('accommodation.step10.show');
        Route::post('accommodation/{id}/step10-inventory-allotment', [AccommodationController::class, 'saveInventoryAllotment'])->name('accommodation.step10.save');
        Route::post('accommodation/{id}/step10-inventory-allotment/{inventoryId}/delete', [AccommodationController::class, 'deleteInventoryAllotment'])->name('accommodation.step10.delete');
        Route::get('accommodation/{id}/step10-inventory-allotment/{inventoryId}/get', [AccommodationController::class, 'getInventoryAllotment'])->name('accommodation.step10.get');
        Route::get('accommodation/{id}/step10-inventory-allotment/{inventoryId}/show', [AccommodationController::class, 'showInventoryAllotment'])->name('accommodation.step10.show_detail');
        Route::get('accommodation/{id}/booking-report', [AccommodationController::class, 'bookingReport'])->name('accommodation.booking-report');

        // Step 11: Promotions & Offers
        Route::get('accommodation/{id}/step11-promotions', [AccommodationController::class, 'step11Promotions'])->name('accommodation.step11.show');
        Route::post('accommodation/{id}/step11-promotions', [AccommodationController::class, 'savePromotion'])->name('accommodation.step11.save');
        Route::post('accommodation/{id}/step11-promotions/{promotionId}/delete', [AccommodationController::class, 'deletePromotion'])->name('accommodation.step11.delete');
        Route::get('accommodation/{id}/step11-promotions/{promotionId}/get', [AccommodationController::class, 'getPromotion'])->name('accommodation.step11.get');

        // Step 12: SEO & Social
        Route::get('accommodation/{id}/step12-seo-social', [AccommodationController::class, 'step12Seo'])->name('accommodation.step12.show');
        Route::post('accommodation/{id}/step12-seo-social', [AccommodationController::class, 'saveStep12Seo'])->name('accommodation.step12.save');

        // Step 13: Publish
        Route::get('accommodation/{id}/step13-publish', [AccommodationController::class, 'step13Publish'])->name('accommodation.step13.show');
        Route::post('accommodation/{id}/submit-for-approval', [AccommodationController::class, 'submitForApproval'])->name('accommodation.submit-approval');

        // Activity Management Routes
        Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
        Route::get('activity/create', [ActivityController::class, 'create'])->name('activity.create');
        Route::post('activity', [ActivityController::class, 'store'])->name('activity.store');

        // Activity Bookings
        Route::get('activity/bookings', [ActivityController::class, 'bookingList'])->name('activity.bookings');
        Route::get('activity/bookings/{booking}', [ActivityController::class, 'bookingDetails'])->name('activity.booking.details');
        Route::post('activity/bookings/{booking}/status', [ActivityController::class, 'updateBookingStatus'])->name('activity.booking.status');

        Route::get('activity/{id}', [ActivityController::class, 'show'])->name('activity.show');
        Route::patch('activity/{id}', [ActivityController::class, 'update'])->name('activity.update');
        
        // Activity Step 1: Basic Information
        Route::get('activity/{id}/step1-basic', [ActivityController::class, 'step1Basic'])->name('activity.step1.show');
        Route::post('activity/{id}/step1-basic', [ActivityController::class, 'saveStep1Basic'])->name('activity.step1.save');
        
        // Activity Step 2: Management & Communication
        Route::get('activity/{id}/step2-management-communication', [ActivityController::class, 'step2ManagementCommunication'])->name('activity.step2.show');
        Route::post('activity/{id}/step2-management-communication', [ActivityController::class, 'saveStep2ManagementCommunication'])->name('activity.step2.save');
        
        // Activity Step 3: Photos & Media
        Route::get('activity/{id}/step3-photos-media', [ActivityController::class, 'step3PhotosMedia'])->name('activity.step3.show');
        Route::post('activity/{id}/step3-photos-media', [ActivityController::class, 'saveStep3PhotosMedia'])->name('activity.step3.save');
        
        // Activity Step 4: Legal & Compliance
        Route::get('activity/{id}/step4-legal-compliance', [ActivityController::class, 'step4LegalCompliance'])->name('activity.step4.show');
        Route::post('activity/{id}/step4-legal-compliance', [ActivityController::class, 'saveStep4LegalCompliance'])->name('activity.step4.save');
        
        // Activity Step 5: Accounting & Transaction
        Route::get('activity/{id}/step5-accounting-transaction', [ActivityController::class, 'step5AccountingTransaction'])->name('activity.step5.show');
        Route::post('activity/{id}/step5-accounting-transaction', [ActivityController::class, 'saveStep5AccountingTransaction'])->name('activity.step5.save');
        
        // Activity Step 6: Policies & Rules
        Route::get('activity/{id}/step6-policies-rules', [ActivityController::class, 'step6PoliciesRules'])->name('activity.step6.show');
        Route::post('activity/{id}/step6-policies-rules', [ActivityController::class, 'saveStep6PoliciesRules'])->name('activity.step6.save');
        
        // Activity Step 7: Variants & Equipment
        Route::get('activity/{id}/step7-variants-equipment', [ActivityController::class, 'step7VariantsEquipment'])->name('activity.step7.show');
        Route::post('activity/{id}/step7-variants-equipment', [ActivityController::class, 'storeVariant'])->name('activity.step7.store');
        Route::get('activity/{id}/step7-variants-equipment/{variantId}/edit', [ActivityController::class, 'editVariant'])->name('activity.step7.edit');
        Route::put('activity/{id}/step7-variants-equipment/{variantId}', [ActivityController::class, 'updateVariant'])->name('activity.step7.update');
        Route::delete('activity/{id}/step7-variants-equipment/{variantId}', [ActivityController::class, 'deleteVariant'])->name('activity.step7.delete');
        Route::post('activity/{id}/step7-operations-staffing', [ActivityController::class, 'saveOperationsStaffing'])->name('activity.step7.operations');
        Route::delete('activity/{id}/step7-operations-staffing/{operationId}', [ActivityController::class, 'deleteOperationsStaffing'])->name('activity.step7.operations.delete');
        Route::put('activity/{id}/step7-operations-staffing/{operationId}', [ActivityController::class, 'updateOperationsStaffing'])->name('activity.step7.operations.update');

        // Step 8: Scheduling TimeSlots
        Route::get('activity/{id}/step8-scheduling-timeslots', [ActivityController::class, 'step8SchedulingTimeSlots'])->name('activity.step8.show');
        Route::post('activity/{id}/step8-scheduling-timeslots', [ActivityController::class, 'storeTimeSlot'])->name('activity.step8.store');
        Route::get('activity/{id}/step8-scheduling-timeslots/{timeslotId}/edit', [ActivityController::class, 'editTimeSlot'])->name('activity.step8.edit');
        Route::put('activity/{id}/step8-scheduling-timeslots/{timeslotId}', [ActivityController::class, 'updateTimeSlot'])->name('activity.step8.update');
        Route::delete('activity/{id}/step8-scheduling-timeslots/{timeslotId}', [ActivityController::class, 'deleteTimeSlot'])->name('activity.step8.delete');
        
        // Step 9: Rates
        Route::get('activity/{id}/step9-rates', [ActivityController::class, 'step9Rates'])->name('activity.step9.show');
        Route::post('activity/{id}/step9-rates', [ActivityController::class, 'storeRate'])->name('activity.step9.store');
        Route::get('activity/{id}/step9-rates/{rateId}/edit', [ActivityController::class, 'editRate'])->name('activity.step9.edit');
        Route::put('activity/{id}/step9-rates/{rateId}', [ActivityController::class, 'updateRate'])->name('activity.step9.update');
        Route::delete('activity/{id}/step9-rates/{rateId}', [ActivityController::class, 'deleteRate'])->name('activity.step9.delete');
        
        // Fees & Add-Ons for Activity
        Route::get('activity/{id}/step9-addons', [ActivityController::class, 'step9Addons'])->name('activity.step9.addons');
        Route::post('activity/{id}/step9-addons', [ActivityController::class, 'storeAddon'])->name('activity.step9.addons.store');
        Route::put('activity/{id}/step9-addons/{addonId}', [ActivityController::class, 'updateAddon'])->name('activity.step9.addons.update');
        Route::delete('activity/{id}/step9-addons/{addonId}', [ActivityController::class, 'deleteAddon'])->name('activity.step9.addons.delete');

        // Step 10: Allotment
        Route::get('activity/{id}/step10-allotment', [ActivityController::class, 'step10Allotment'])->name('activity.step10.show');
        Route::post('activity/{id}/step10-allotment', [ActivityController::class, 'storeAllotment'])->name('activity.step10.store');
        Route::put('activity/{id}/step10-allotment/{allotmentId}', [ActivityController::class, 'updateAllotment'])->name('activity.step10.update');
        Route::delete('activity/{id}/step10-allotment/{allotmentId}', [ActivityController::class, 'deleteAllotment'])->name('activity.step10.delete');
        Route::post('activity/{id}/step10-allotment/blackout', [ActivityController::class, 'storeBlackoutDate'])->name('activity.step10.blackout.store');
        Route::delete('activity/{id}/step10-allotment/blackout/{blackoutId}', [ActivityController::class, 'deleteBlackoutDate'])->name('activity.step10.blackout.delete');

        // Step 11: Promotions & Offers
        Route::get('activity/{id}/step11-promotions', [ActivityController::class, 'step11PromotionsOffers'])->name('activity.step11.show');
        Route::post('activity/{id}/step11-promotions', [ActivityController::class, 'storePromotion'])->name('activity.step11.store');
        Route::put('activity/{id}/step11-promotions/{promotionId}', [ActivityController::class, 'updatePromotion'])->name('activity.step11.update');
        Route::delete('activity/{id}/step11-promotions/{promotionId}', [ActivityController::class, 'deletePromotion'])->name('activity.step11.delete');

        // Step 12: SEO & Social
        Route::get('activity/{id}/step12-seo-social', [ActivityController::class, 'step12SeoSocial'])->name('activity.step12.show');
        Route::post('activity/{id}/step12-seo-social', [ActivityController::class, 'storeSeoSocial'])->name('activity.step12.store');

        // Step 13: Publish
        Route::get('activity/{id}/step13-publish', [ActivityController::class, 'step13Publish'])->name('activity.step13.show');
        Route::post('activity/{id}/submit-for-approval', [ActivityController::class, 'submitForApproval'])->name('activity.submit-approval');
        
        Route::delete('activity/{id}', [ActivityController::class, 'destroy'])->name('activity.destroy');

        // Shared cart links
        Route::get('shared-carts', [SharedCartController::class, 'index'])->name('shared-carts.index');
        Route::get('shared-carts/create', [SharedCartController::class, 'create'])->name('shared-carts.create');
        Route::post('shared-carts', [SharedCartController::class, 'store'])->name('shared-carts.store');
        Route::get('shared-carts/{sharedCart}', [SharedCartController::class, 'show'])->name('shared-carts.show');
        Route::post('shared-carts/{sharedCart}/items', [SharedCartController::class, 'storeItem'])->name('shared-carts.items.store');
        Route::post('shared-carts/{sharedCart}/items/{itemKey}/remove', [SharedCartController::class, 'removeItem'])->name('shared-carts.items.remove');

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
