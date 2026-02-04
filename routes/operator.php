<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operator\AuthController;
use App\Http\Controllers\Operator\ProfileController;
use App\Http\Controllers\Operator\RegistrationController;

Route::prefix('operator')->name('operator.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:operator')->group(function () {
        Route::get('profile', [ProfileController::class, 'showProfile'])->name('profile');
        // Registration/Profile Steps
        // Step 1 (Set Password) skipped after registration
        Route::get('register/step2-profile', [RegistrationController::class, 'step2Profile'])->name('register.step2');
        Route::post('register/step2-profile', [RegistrationController::class, 'saveStep2Profile']);
        Route::get('register/step3-legal', [RegistrationController::class, 'step3Legal'])->name('register.step3');
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
