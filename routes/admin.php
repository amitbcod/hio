<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SharedCartController;
use App\Http\Controllers\Admin\AdminSettingsController;

// Public admin auth routes (no middleware)
Route::get('admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('admin/login', [AdminAuthController::class, 'login']);

// Protected admin routes - require admin session
Route::prefix('admin')->name('admin.')->middleware(\App\Http\Middleware\AdminAuthMiddleware::class)->group(function () {
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    // simple session-based guard (controller checks session before actions)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
    Route::post('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::post('businesses/{business}/approve', [DashboardController::class, 'approveBusiness'])->name('business.approve');
    Route::post('businesses/{business}/reject', [DashboardController::class, 'rejectBusiness'])->name('business.reject');
    Route::post('mpos/{mpo}/approve', [DashboardController::class, 'approveMpo'])->name('mpo.approve');
    Route::post('mpos/{mpo}/reject', [DashboardController::class, 'rejectMpo'])->name('mpo.reject');
    Route::post('accommodations/{accommodation}/approve', [DashboardController::class, 'approveAccommodation'])->name('accommodation.approve');
    Route::post('accommodations/{accommodation}/reject', [DashboardController::class, 'rejectAccommodation'])->name('accommodation.reject');
    Route::post('activities/{activity}/approve', [DashboardController::class, 'approveActivity'])->name('activity.approve');
    Route::post('activities/{activity}/reject', [DashboardController::class, 'rejectActivity'])->name('activity.reject');

    // Admin transport approvals
    Route::post('transports/{transport}/approve', [DashboardController::class, 'approveTransport'])->name('transport.approve');
    Route::post('transports/{transport}/reject', [DashboardController::class, 'rejectTransport'])->name('transport.reject');

    // Admin role management (create global / business-scoped roles)
    Route::get('roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
    Route::post('roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');

    // Admin modules management (CRUD)
    Route::get('modules', [\App\Http\Controllers\Admin\ModuleController::class, 'index'])->name('modules.index');
    Route::get('modules/create', [\App\Http\Controllers\Admin\ModuleController::class, 'create'])->name('modules.create');
    Route::post('modules', [\App\Http\Controllers\Admin\ModuleController::class, 'store'])->name('modules.store');
    Route::get('modules/{module}/edit', [\App\Http\Controllers\Admin\ModuleController::class, 'edit'])->name('modules.edit');
    Route::post('modules/{module}', [\App\Http\Controllers\Admin\ModuleController::class, 'update'])->name('modules.update');
    Route::delete('modules/{module}', [\App\Http\Controllers\Admin\ModuleController::class, 'destroy'])->name('modules.destroy');

    // Admin operators management (CRUD)
    Route::get('operators', [\App\Http\Controllers\Admin\OperatorController::class, 'index'])->name('operators.index');
    Route::get('operators/create', [\App\Http\Controllers\Admin\OperatorController::class, 'create'])->name('operators.create');
    Route::post('operators', [\App\Http\Controllers\Admin\OperatorController::class, 'store'])->name('operators.store');
    Route::get('operators/{operator}/edit', [\App\Http\Controllers\Admin\OperatorController::class, 'edit'])->name('operators.edit');
    Route::post('operators/{operator}', [\App\Http\Controllers\Admin\OperatorController::class, 'update'])->name('operators.update');
    Route::post('operators/{operator}/select', [\App\Http\Controllers\Admin\OperatorController::class, 'select'])->name('operators.select');
    Route::delete('operators/{operator}', [\App\Http\Controllers\Admin\OperatorController::class, 'destroy'])->name('operators.destroy');

    // Admin travellers management
    Route::get('travellers', [\App\Http\Controllers\Admin\TravelerController::class, 'index'])->name('travellers.index');
    Route::get('travellers/{traveler}/edit', [\App\Http\Controllers\Admin\TravelerController::class, 'edit'])->name('travellers.edit');
    Route::post('travellers/{traveler}', [\App\Http\Controllers\Admin\TravelerController::class, 'update'])->name('travellers.update');
    Route::post('travellers/{traveler}/suspend', [\App\Http\Controllers\Admin\TravelerController::class, 'suspend'])->name('travellers.suspend');
    Route::post('travellers/{traveler}/create-booking', [\App\Http\Controllers\Admin\TravelerController::class, 'createBooking'])->name('travellers.create-booking');

    // Admin trips management
    Route::get('trips', [\App\Http\Controllers\Admin\TripController::class, 'index'])->name('trips.index');
    Route::get('trips/create', [\App\Http\Controllers\Admin\TripController::class, 'create'])->name('trips.create');
    Route::post('trips', [\App\Http\Controllers\Admin\TripController::class, 'store'])->name('trips.store');
    Route::get('trips/{trip}', [\App\Http\Controllers\Admin\TripController::class, 'show'])->name('trips.show');
    Route::get('trips/{trip}/edit', [\App\Http\Controllers\Admin\TripController::class, 'edit'])->name('trips.edit');
    Route::post('trips/{trip}', [\App\Http\Controllers\Admin\TripController::class, 'update'])->name('trips.update');

    // Admin feedback management
    Route::get('feedback', [\App\Http\Controllers\Admin\FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('feedback/{review}', [\App\Http\Controllers\Admin\FeedbackController::class, 'show'])->name('feedback.show');
    Route::post('feedback/item/{item}/status', [\App\Http\Controllers\Admin\FeedbackController::class, 'updateItemStatus'])->name('feedback.item.update-status');

    // Admin accommodation booking management (superadmin)
    Route::get('accommodation/bookings', [\App\Http\Controllers\Admin\AccommodationBookingController::class, 'index'])->name('accommodation.bookings');
    Route::get('accommodation/bookings/{booking}', [\App\Http\Controllers\Admin\AccommodationBookingController::class, 'show'])->name('accommodation.booking.details');

    // Admin activity booking management (superadmin)
    Route::get('activity/bookings', [\App\Http\Controllers\Admin\ActivityBookingController::class, 'index'])->name('activity.bookings');
    Route::get('activity/bookings/{booking}', [\App\Http\Controllers\Admin\ActivityBookingController::class, 'show'])->name('activity.booking.details');

    // Admin transport booking management (superadmin)
    Route::get('transport/bookings', [\App\Http\Controllers\Admin\TransportBookingController::class, 'index'])->name('transport.bookings');
    Route::get('transport/bookings/{booking}', [\App\Http\Controllers\Admin\TransportBookingController::class, 'show'])->name('transport.booking.details');

    // Admin accommodation management for selected operator/business
    Route::get('accommodations/select-operator', [\App\Http\Controllers\Admin\AccommodationController::class, 'selectOperator'])->name('accommodation.select-operator');
    Route::post('accommodations/select-operator', [\App\Http\Controllers\Admin\AccommodationController::class, 'setOperator'])->name('accommodation.set-operator');
    Route::get('accommodations', [\App\Http\Controllers\Admin\AccommodationController::class, 'index'])->name('accommodation.index');
    Route::get('accommodations/create', [\App\Http\Controllers\Admin\AccommodationController::class, 'create'])->name('accommodation.create');
    Route::post('accommodations', [\App\Http\Controllers\Admin\AccommodationController::class, 'store'])->name('accommodation.store');
    Route::get('accommodations/{id}', [\App\Http\Controllers\Admin\AccommodationController::class, 'show'])->name('accommodation.show');
    Route::get('accommodations/{id}/edit/step1', [\App\Http\Controllers\Admin\AccommodationController::class, 'editStep1'])->name('accommodation.step1.edit');
    Route::put('accommodations/{id}', [\App\Http\Controllers\Admin\AccommodationController::class, 'update'])->name('accommodation.update');
    Route::get('accommodations/{id}/step2-reservation', [\App\Http\Controllers\Admin\AccommodationController::class, 'step2Reservation'])->name('accommodation.step2.show');
    Route::post('accommodations/{id}/step2-reservation', [\App\Http\Controllers\Admin\AccommodationController::class, 'saveStep2'])->name('accommodation.saveStep2');
    Route::get('accommodations/{id}/step3-photos', [\App\Http\Controllers\Admin\AccommodationController::class, 'step3Photos'])->name('accommodation.step3.show');
    Route::post('accommodations/{id}/step3-photos', [\App\Http\Controllers\Admin\AccommodationController::class, 'saveStep3Photos'])->name('accommodation.saveStep3');
    Route::post('accommodations/{id}/media/{mediaId}/delete', [\App\Http\Controllers\Admin\AccommodationController::class, 'deleteMedia'])->name('accommodation.media.delete');
    Route::get('accommodations/{id}/step4-compliance', [\App\Http\Controllers\Admin\AccommodationController::class, 'step4Compliance'])->name('accommodation.step4.show');
    Route::post('accommodations/{id}/step4-compliance', [\App\Http\Controllers\Admin\AccommodationController::class, 'saveStep4Compliance'])->name('accommodation.saveStep4');
    Route::get('accommodations/{id}/step5-accounting', [\App\Http\Controllers\Admin\AccommodationController::class, 'step5Accounting'])->name('accommodation.step5.show');
    Route::post('accommodations/{id}/step5-accounting', [\App\Http\Controllers\Admin\AccommodationController::class, 'saveStep5Accounting'])->name('accommodation.saveStep5');
    Route::get('accommodations/{id}/step6-policies-rules', [\App\Http\Controllers\Admin\AccommodationController::class, 'step6PoliciesRules'])->name('accommodation.step6.show');
    Route::post('accommodations/{id}/step6-policies-rules', [\App\Http\Controllers\Admin\AccommodationController::class, 'saveStep6PoliciesRules'])->name('accommodation.saveStep6');
    Route::get('accommodations/{id}/step7-rooms', [\App\Http\Controllers\Admin\AccommodationController::class, 'step7RoomsUnits'])->name('accommodation.step7.show');
    Route::post('accommodations/{id}/step7-rooms', [\App\Http\Controllers\Admin\AccommodationController::class, 'saveRoom'])->name('accommodation.saveStep7');
    Route::get('accommodations/{id}/step7-rooms/{room}/edit', [\App\Http\Controllers\Admin\AccommodationController::class, 'editRoom'])->name('accommodation.step7.room.edit');
    Route::post('accommodations/{id}/step7-rooms/{room}/edit', [\App\Http\Controllers\Admin\AccommodationController::class, 'updateRoom'])->name('accommodation.step7.room.update');
    Route::post('accommodations/{id}/step7-rooms/{room}/delete', [\App\Http\Controllers\Admin\AccommodationController::class, 'deleteRoom'])->name('accommodation.step7.room.delete');
    Route::get('accommodations/{id}/step8-rate-plans', [\App\Http\Controllers\Admin\AccommodationController::class, 'step8RatePlans'])->name('accommodation.step8.show');
    Route::post('accommodations/{id}/step8-rate-plans', [\App\Http\Controllers\Admin\AccommodationController::class, 'saveRatePlan'])->name('accommodation.saveStep8');
    Route::get('accommodations/{id}/step8-rate-plans/{plan}/edit', [\App\Http\Controllers\Admin\AccommodationController::class, 'editRatePlan'])->name('accommodation.step8.plan.edit');
    Route::put('accommodations/{id}/step8-rate-plans/{plan}/edit', [\App\Http\Controllers\Admin\AccommodationController::class, 'updateRatePlan'])->name('accommodation.step8.plan.update');
    Route::post('accommodations/{id}/step8-rate-plans/{plan}/delete', [\App\Http\Controllers\Admin\AccommodationController::class, 'deleteRatePlan'])->name('accommodation.step8.plan.delete');
    Route::post('accommodations/{id}/step8-assign-plans', [\App\Http\Controllers\Admin\AccommodationController::class, 'assignPlansToRoom'])->name('accommodation.step8.assignPlans')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('accommodations/{id}/step8-remove-plan', [\App\Http\Controllers\Admin\AccommodationController::class, 'removePlanFromRoom'])->name('accommodation.step8.removePlan')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    Route::get('accommodations/{id}/step9-season-pricing', [\App\Http\Controllers\Admin\AccommodationController::class, 'step9SeasonPricing'])->name('accommodation.step9.show');
    Route::post('accommodations/{id}/step9-season-pricing', [\App\Http\Controllers\Admin\AccommodationController::class, 'saveSeasonPricing'])->name('accommodation.saveStep9');
    Route::post('accommodations/{id}/step9-set-default-price', [\App\Http\Controllers\Admin\AccommodationController::class, 'setDefaultPrice'])->name('accommodation.step9.setDefaultPrice');
    Route::post('accommodations/{id}/step9-add-season', [\App\Http\Controllers\Admin\AccommodationController::class, 'addSeasonalEntry'])->name('accommodation.step9.addSeason');
    Route::post('accommodations/{id}/step9-delete-season/{entryId}', [\App\Http\Controllers\Admin\AccommodationController::class, 'deleteSeasonalEntry'])->name('accommodation.step9.deleteSeason');
    Route::post('accommodations/{id}/step9-update-season/{entryId}', [\App\Http\Controllers\Admin\AccommodationController::class, 'updateSeasonalEntry'])->name('accommodation.step9.updateSeason');
    Route::get('accommodations/{id}/step9-season-pricing/{pricing}/edit', [\App\Http\Controllers\Admin\AccommodationController::class, 'editSeasonPricing'])->name('accommodation.step9.pricing.edit');
    Route::post('accommodations/{id}/step9-season-pricing/{pricing}/edit', [\App\Http\Controllers\Admin\AccommodationController::class, 'updateSeasonPricing'])->name('accommodation.step9.pricing.update');
    Route::post('accommodations/{id}/step9-season-pricing/{pricing}/delete', [\App\Http\Controllers\Admin\AccommodationController::class, 'deleteSeasonPricing'])->name('accommodation.step9.pricing.delete');
    Route::get('accommodations/{id}/step10-inventory-allotment', [\App\Http\Controllers\Admin\AccommodationController::class, 'step10InventoryAllotment'])->name('accommodation.step10.show');
    Route::post('accommodations/{id}/step10-inventory-allotment', [\App\Http\Controllers\Admin\AccommodationController::class, 'saveInventoryAllotment'])->name('accommodation.step10.save');
    Route::post('accommodations/{id}/step10-inventory-allotment/{inventoryId}/delete', [\App\Http\Controllers\Admin\AccommodationController::class, 'deleteInventoryAllotment'])->name('accommodation.step10.delete');
    Route::get('accommodations/{id}/step10-inventory-allotment/{inventoryId}/get', [\App\Http\Controllers\Admin\AccommodationController::class, 'getInventoryAllotment'])->name('accommodation.step10.get');
    Route::get('accommodations/{id}/step10-inventory-allotment/{inventoryId}/show', [\App\Http\Controllers\Admin\AccommodationController::class, 'showInventoryAllotment'])->name('accommodation.step10.show_detail');
    Route::get('accommodations/{id}/booking-report', [\App\Http\Controllers\Admin\AccommodationController::class, 'bookingReport'])->name('accommodation.booking-report');
    Route::get('accommodations/{id}/step11-promotions', [\App\Http\Controllers\Admin\AccommodationController::class, 'step11Promotions'])->name('accommodation.step11.show');
    Route::post('accommodations/{id}/step11-promotions', [\App\Http\Controllers\Admin\AccommodationController::class, 'savePromotion'])->name('accommodation.step11.save');
    Route::post('accommodations/{id}/step11-promotions/{promotionId}/delete', [\App\Http\Controllers\Admin\AccommodationController::class, 'deletePromotion'])->name('accommodation.step11.delete');
    Route::get('accommodations/{id}/step11-promotions/{promotionId}/get', [\App\Http\Controllers\Admin\AccommodationController::class, 'getPromotion'])->name('accommodation.step11.get');
    Route::get('accommodations/{id}/step12-seo-social', [\App\Http\Controllers\Admin\AccommodationController::class, 'step12Seo'])->name('accommodation.step12.show');
    Route::post('accommodations/{id}/step12-seo-social', [\App\Http\Controllers\Admin\AccommodationController::class, 'saveStep12Seo'])->name('accommodation.step12.save');
    Route::get('accommodations/{id}/step13-publish', [\App\Http\Controllers\Admin\AccommodationController::class, 'step13Publish'])->name('accommodation.step13.show');
    Route::post('accommodations/{id}/submit-for-approval', [\App\Http\Controllers\Admin\AccommodationController::class, 'submitForApproval'])->name('accommodation.submit-approval');

    // Admin activity management for selected operator/business
    Route::get('activity', [\App\Http\Controllers\Admin\ActivityController::class, 'index'])->name('activity.index');
    Route::get('activity/create', [\App\Http\Controllers\Admin\ActivityController::class, 'create'])->name('activity.create');
    Route::post('activity', [\App\Http\Controllers\Admin\ActivityController::class, 'store'])->name('activity.store');
    Route::get('activity/{id}', [\App\Http\Controllers\Admin\ActivityController::class, 'show'])->name('activity.show');
    Route::patch('activity/{id}', [\App\Http\Controllers\Admin\ActivityController::class, 'update'])->name('activity.update');
    Route::get('activity/{id}/step1-basic', [\App\Http\Controllers\Admin\ActivityController::class, 'step1Basic'])->name('activity.step1.show');
    Route::post('activity/{id}/step1-basic', [\App\Http\Controllers\Admin\ActivityController::class, 'saveStep1Basic'])->name('activity.step1.save');
    Route::get('activity/{id}/step2-management-communication', [\App\Http\Controllers\Admin\ActivityController::class, 'step2ManagementCommunication'])->name('activity.step2.show');
    Route::post('activity/{id}/step2-management-communication', [\App\Http\Controllers\Admin\ActivityController::class, 'saveStep2ManagementCommunication'])->name('activity.step2.save');
    Route::get('activity/{id}/step3-photos-media', [\App\Http\Controllers\Admin\ActivityController::class, 'step3PhotosMedia'])->name('activity.step3.show');
    Route::post('activity/{id}/step3-photos-media', [\App\Http\Controllers\Admin\ActivityController::class, 'saveStep3PhotosMedia'])->name('activity.step3.save');
    Route::get('activity/{id}/step4-legal-compliance', [\App\Http\Controllers\Admin\ActivityController::class, 'step4LegalCompliance'])->name('activity.step4.show');
    Route::post('activity/{id}/step4-legal-compliance', [\App\Http\Controllers\Admin\ActivityController::class, 'saveStep4LegalCompliance'])->name('activity.step4.save');
    Route::get('activity/{id}/step5-accounting-transaction', [\App\Http\Controllers\Admin\ActivityController::class, 'step5AccountingTransaction'])->name('activity.step5.show');
    Route::post('activity/{id}/step5-accounting-transaction', [\App\Http\Controllers\Admin\ActivityController::class, 'saveStep5AccountingTransaction'])->name('activity.step5.save');
    Route::get('activity/{id}/step6-policies-rules', [\App\Http\Controllers\Admin\ActivityController::class, 'step6PoliciesRules'])->name('activity.step6.show');
    Route::post('activity/{id}/step6-policies-rules', [\App\Http\Controllers\Admin\ActivityController::class, 'saveStep6PoliciesRules'])->name('activity.step6.save');
    Route::get('activity/{id}/step7-variants-equipment', [\App\Http\Controllers\Admin\ActivityController::class, 'step7VariantsEquipment'])->name('activity.step7.show');
    Route::post('activity/{id}/step7-variants-equipment', [\App\Http\Controllers\Admin\ActivityController::class, 'storeVariant'])->name('activity.step7.store');
    Route::get('activity/{id}/step7-variants-equipment/{variantId}/edit', [\App\Http\Controllers\Admin\ActivityController::class, 'editVariant'])->name('activity.step7.edit');
    Route::put('activity/{id}/step7-variants-equipment/{variantId}', [\App\Http\Controllers\Admin\ActivityController::class, 'updateVariant'])->name('activity.step7.update');
    Route::delete('activity/{id}/step7-variants-equipment/{variantId}', [\App\Http\Controllers\Admin\ActivityController::class, 'deleteVariant'])->name('activity.step7.delete');
    Route::post('activity/{id}/step7-operations-staffing', [\App\Http\Controllers\Admin\ActivityController::class, 'saveOperationsStaffing'])->name('activity.step7.operations');
    Route::delete('activity/{id}/step7-operations-staffing/{operationId}', [\App\Http\Controllers\Admin\ActivityController::class, 'deleteOperationsStaffing'])->name('activity.step7.operations.delete');
    Route::put('activity/{id}/step7-operations-staffing/{operationId}', [\App\Http\Controllers\Admin\ActivityController::class, 'updateOperationsStaffing'])->name('activity.step7.operations.update');
    Route::get('activity/{id}/step8-scheduling-timeslots', [\App\Http\Controllers\Admin\ActivityController::class, 'step8SchedulingTimeSlots'])->name('activity.step8.show');
    Route::post('activity/{id}/step8-scheduling-timeslots', [\App\Http\Controllers\Admin\ActivityController::class, 'storeTimeSlot'])->name('activity.step8.store');
    Route::get('activity/{id}/step8-scheduling-timeslots/{timeslotId}/edit', [\App\Http\Controllers\Admin\ActivityController::class, 'editTimeSlot'])->name('activity.step8.edit');
    Route::put('activity/{id}/step8-scheduling-timeslots/{timeslotId}', [\App\Http\Controllers\Admin\ActivityController::class, 'updateTimeSlot'])->name('activity.step8.update');
    Route::delete('activity/{id}/step8-scheduling-timeslots/{timeslotId}', [\App\Http\Controllers\Admin\ActivityController::class, 'deleteTimeSlot'])->name('activity.step8.delete');
    Route::get('activity/{id}/step9-rates', [\App\Http\Controllers\Admin\ActivityController::class, 'step9Rates'])->name('activity.step9.show');
    Route::post('activity/{id}/step9-rates', [\App\Http\Controllers\Admin\ActivityController::class, 'storeRate'])->name('activity.step9.store');
    Route::get('activity/{id}/step9-rates/{rateId}/edit', [\App\Http\Controllers\Admin\ActivityController::class, 'editRate'])->name('activity.step9.edit');
    Route::put('activity/{id}/step9-rates/{rateId}', [\App\Http\Controllers\Admin\ActivityController::class, 'updateRate'])->name('activity.step9.update');
    Route::delete('activity/{id}/step9-rates/{rateId}', [\App\Http\Controllers\Admin\ActivityController::class, 'deleteRate'])->name('activity.step9.delete');
    Route::get('activity/{id}/step9-addons', [\App\Http\Controllers\Admin\ActivityController::class, 'step9Addons'])->name('activity.step9.addons');
    Route::post('activity/{id}/step9-addons', [\App\Http\Controllers\Admin\ActivityController::class, 'storeAddon'])->name('activity.step9.addons.store');
    Route::put('activity/{id}/step9-addons/{addonId}', [\App\Http\Controllers\Admin\ActivityController::class, 'updateAddon'])->name('activity.step9.addons.update');
    Route::delete('activity/{id}/step9-addons/{addonId}', [\App\Http\Controllers\Admin\ActivityController::class, 'deleteAddon'])->name('activity.step9.addons.delete');
    Route::get('activity/{id}/step10-allotment', [\App\Http\Controllers\Admin\ActivityController::class, 'step10Allotment'])->name('activity.step10.show');
    Route::post('activity/{id}/step10-allotment', [\App\Http\Controllers\Admin\ActivityController::class, 'storeAllotment'])->name('activity.step10.store');
    Route::put('activity/{id}/step10-allotment/{allotmentId}', [\App\Http\Controllers\Admin\ActivityController::class, 'updateAllotment'])->name('activity.step10.update');
    Route::delete('activity/{id}/step10-allotment/{allotmentId}', [\App\Http\Controllers\Admin\ActivityController::class, 'deleteAllotment'])->name('activity.step10.delete');
    Route::post('activity/{id}/step10-allotment/blackout', [\App\Http\Controllers\Admin\ActivityController::class, 'storeBlackoutDate'])->name('activity.step10.blackout.store');
    Route::delete('activity/{id}/step10-allotment/blackout/{blackoutId}', [\App\Http\Controllers\Admin\ActivityController::class, 'deleteBlackoutDate'])->name('activity.step10.blackout.delete');
    Route::get('activity/{id}/step11-promotions', [\App\Http\Controllers\Admin\ActivityController::class, 'step11PromotionsOffers'])->name('activity.step11.show');
    Route::post('activity/{id}/step11-promotions', [\App\Http\Controllers\Admin\ActivityController::class, 'storePromotion'])->name('activity.step11.store');
    Route::put('activity/{id}/step11-promotions/{promotionId}', [\App\Http\Controllers\Admin\ActivityController::class, 'updatePromotion'])->name('activity.step11.update');
    Route::delete('activity/{id}/step11-promotions/{promotionId}', [\App\Http\Controllers\Admin\ActivityController::class, 'deletePromotion'])->name('activity.step11.delete');
    Route::get('activity/{id}/step12-seo-social', [\App\Http\Controllers\Admin\ActivityController::class, 'step12SeoSocial'])->name('activity.step12.show');
    Route::post('activity/{id}/step12-seo-social', [\App\Http\Controllers\Admin\ActivityController::class, 'storeSeoSocial'])->name('activity.step12.store');
    Route::get('activity/{id}/step13-publish', [\App\Http\Controllers\Admin\ActivityController::class, 'step13Publish'])->name('activity.step13.show');
    Route::post('activity/{id}/submit-for-approval', [\App\Http\Controllers\Admin\ActivityController::class, 'submitForApproval'])->name('activity.submit-approval');
    Route::delete('activity/{id}', [\App\Http\Controllers\Admin\ActivityController::class, 'destroy'])->name('activity.destroy');

    // Save accommodation fees
    Route::post('accommodations/{id}/fees', [\App\Http\Controllers\Admin\AccommodationController::class, 'saveFees'])->name('accommodation.fees.save');
    Route::get('accommodations/{id}/fees', [\App\Http\Controllers\Admin\AccommodationController::class, 'getFees'])->name('accommodation.fees.get');

    // Additional fees routes (preferred name)
    Route::post('accommodations/{id}/additional-fees', [\App\Http\Controllers\Admin\AccommodationController::class, 'saveFees'])->name('accommodation.additional_fees.save');
    Route::get('accommodations/{id}/additional-fees', [\App\Http\Controllers\Admin\AccommodationController::class, 'getFees'])->name('accommodation.additional_fees.get');

    // Admin payment transactions management
    Route::get('payment-transactions', [\App\Http\Controllers\Admin\PaymentTransactionController::class, 'index'])->name('payment-transactions.index');
    Route::get('payment-transactions/{transaction}', [\App\Http\Controllers\Admin\PaymentTransactionController::class, 'show'])->name('payment-transactions.show');
    Route::post('payment-transactions/{transaction}/callbacks', [\App\Http\Controllers\Admin\PaymentTransactionController::class, 'getCallbacks'])->name('payment-transactions.callbacks');

    Route::get('shared-carts', [SharedCartController::class, 'index'])->name('shared-carts.index');
    Route::get('shared-carts/create', [SharedCartController::class, 'create'])->name('shared-carts.create');
    Route::post('shared-carts', [SharedCartController::class, 'store'])->name('shared-carts.store');
    Route::get('shared-carts/{sharedCart}', [SharedCartController::class, 'show'])->name('shared-carts.show');
    Route::post('shared-carts/{sharedCart}/items', [SharedCartController::class, 'storeItem'])->name('shared-carts.items.store');
    Route::post('shared-carts/{sharedCart}/items/{itemKey}/remove', [SharedCartController::class, 'removeItem'])->name('shared-carts.items.remove');

    // Policy templates (HIO) - admin CRUD
    Route::get('policy-templates', [\App\Http\Controllers\Admin\PolicyTemplateController::class, 'index'])->name('policy-templates.index');
    Route::get('policy-templates/create', [\App\Http\Controllers\Admin\PolicyTemplateController::class, 'create'])->name('policy-templates.create');
    Route::post('policy-templates', [\App\Http\Controllers\Admin\PolicyTemplateController::class, 'store'])->name('policy-templates.store');
    Route::get('policy-templates/{policyTemplate}/edit', [\App\Http\Controllers\Admin\PolicyTemplateController::class, 'edit'])->name('policy-templates.edit');
    Route::match(['put','patch','post'], 'policy-templates/{policyTemplate}', [\App\Http\Controllers\Admin\PolicyTemplateController::class, 'update'])->name('policy-templates.update');
    Route::delete('policy-templates/{policyTemplate}', [\App\Http\Controllers\Admin\PolicyTemplateController::class, 'destroy'])->name('policy-templates.destroy');

    // Static pages - admin CRUD
    Route::get('static-pages', [\App\Http\Controllers\Admin\StaticPageController::class, 'index'])->name('static-pages.index');
    Route::get('static-pages/create', [\App\Http\Controllers\Admin\StaticPageController::class, 'create'])->name('static-pages.create');
    Route::post('static-pages', [\App\Http\Controllers\Admin\StaticPageController::class, 'store'])->name('static-pages.store');
    Route::get('static-pages/{staticPage}/edit', [\App\Http\Controllers\Admin\StaticPageController::class, 'edit'])->name('static-pages.edit');
    Route::match(['put','patch','post'], 'static-pages/{staticPage}', [\App\Http\Controllers\Admin\StaticPageController::class, 'update'])->name('static-pages.update');
    Route::delete('static-pages/{staticPage}', [\App\Http\Controllers\Admin\StaticPageController::class, 'destroy'])->name('static-pages.destroy');

    Route::get('places', [\App\Http\Controllers\Admin\PlaceController::class, 'index'])->name('places.index');
    Route::get('places/create', [\App\Http\Controllers\Admin\PlaceController::class, 'create'])->name('places.create');
    Route::post('places', [\App\Http\Controllers\Admin\PlaceController::class, 'store'])->name('places.store');
    Route::get('places/{place}/edit', [\App\Http\Controllers\Admin\PlaceController::class, 'edit'])->name('places.edit');
    Route::put('places/{place}', [\App\Http\Controllers\Admin\PlaceController::class, 'update'])->name('places.update');
    Route::delete('places/{place}', [\App\Http\Controllers\Admin\PlaceController::class, 'destroy'])->name('places.destroy');

    // Admin package management (multi-step). Step 1: package creation
    Route::get('packages', [\App\Http\Controllers\Admin\PackageController::class, 'index'])->name('packages.index');
    Route::get('packages/create', [\App\Http\Controllers\Admin\PackageController::class, 'create'])->name('packages.create');
    Route::post('packages', [\App\Http\Controllers\Admin\PackageController::class, 'store'])->name('packages.store');
    Route::get('packages/{package}/edit', [\App\Http\Controllers\Admin\PackageController::class, 'edit'])->name('packages.edit');
    Route::post('packages/{package}', [\App\Http\Controllers\Admin\PackageController::class, 'update'])->name('packages.update');
    Route::get('packages/{package}/step2', [\App\Http\Controllers\Admin\PackageController::class, 'step2'])->name('packages.step2');
    Route::post('packages/{package}/step2', [\App\Http\Controllers\Admin\PackageController::class, 'storeStep2'])->name('packages.step2.store');
    Route::get('packages/{package}/step3', [\App\Http\Controllers\Admin\PackageController::class, 'step3'])->name('packages.step3');
    Route::post('packages/{package}/step3', [\App\Http\Controllers\Admin\PackageController::class, 'storeStep3'])->name('packages.step3.store');
    Route::get('packages/{package}/step4', [\App\Http\Controllers\Admin\PackageController::class, 'step4'])->name('packages.step4');
    Route::post('packages/{package}/step4', [\App\Http\Controllers\Admin\PackageController::class, 'storeStep4'])->name('packages.step4.store');
    Route::get('packages/{package}/step5', [\App\Http\Controllers\Admin\PackageController::class, 'step5'])->name('packages.step5');
    Route::post('packages/{package}/step5', [\App\Http\Controllers\Admin\PackageController::class, 'storeStep5'])->name('packages.step5.store');
    Route::get('packages/{package}/step6', [\App\Http\Controllers\Admin\PackageController::class, 'step6'])->name('packages.step6');
    Route::post('packages/{package}/step6', [\App\Http\Controllers\Admin\PackageController::class, 'storeStep6'])->name('packages.step6.store');

    Route::get('vehicle-types', [\App\Http\Controllers\Admin\TransportVehicleTypeController::class, 'index'])->name('vehicle-types.index');
    Route::get('vehicle-types/create', [\App\Http\Controllers\Admin\TransportVehicleTypeController::class, 'create'])->name('vehicle-types.create');
    Route::post('vehicle-types', [\App\Http\Controllers\Admin\TransportVehicleTypeController::class, 'store'])->name('vehicle-types.store');
    Route::get('vehicle-types/{vehicleType}/edit', [\App\Http\Controllers\Admin\TransportVehicleTypeController::class, 'edit'])->name('vehicle-types.edit');
    Route::put('vehicle-types/{vehicleType}', [\App\Http\Controllers\Admin\TransportVehicleTypeController::class, 'update'])->name('vehicle-types.update');
    Route::delete('vehicle-types/{vehicleType}', [\App\Http\Controllers\Admin\TransportVehicleTypeController::class, 'destroy'])->name('vehicle-types.destroy');

    // Admin regions management (CRUD)
    Route::get('regions', [\App\Http\Controllers\Admin\RegionController::class, 'index'])->name('regions.index');
    Route::get('regions/create', [\App\Http\Controllers\Admin\RegionController::class, 'create'])->name('regions.create');
    Route::post('regions', [\App\Http\Controllers\Admin\RegionController::class, 'store'])->name('regions.store');
    Route::get('regions/{region}/edit', [\App\Http\Controllers\Admin\RegionController::class, 'edit'])->name('regions.edit');
    Route::post('regions/{region}', [\App\Http\Controllers\Admin\RegionController::class, 'update'])->name('regions.update');
    Route::delete('regions/{region}', [\App\Http\Controllers\Admin\RegionController::class, 'destroy'])->name('regions.destroy');

    Route::get('transport-service-route-pairs', [\App\Http\Controllers\Admin\TransportServiceRoutePairController::class, 'index'])->name('transport-service-route-pairs.index');
    Route::post('transport-service-route-pairs', [\App\Http\Controllers\Admin\TransportServiceRoutePairController::class, 'store'])->name('transport-service-route-pairs.store');
    Route::match(['put', 'patch', 'post'], 'transport-service-route-pairs/{transportServiceRoutePair}', [\App\Http\Controllers\Admin\TransportServiceRoutePairController::class, 'update'])->name('transport-service-route-pairs.update');
    Route::delete('transport-service-route-pairs/{transportServiceRoutePair}', [\App\Http\Controllers\Admin\TransportServiceRoutePairController::class, 'destroy'])->name('transport-service-route-pairs.destroy');
});
