<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mpos', function (Blueprint $table) {
            if (!Schema::hasColumn('mpos', 'user_type')) {
                $table->enum('user_type', ['Operator', 'MPO', 'Agent'])->default('MPO')->after('mpo_id');
            }

            if (!Schema::hasColumn('mpos', 'is_owner')) {
                $table->enum('is_owner', ['yes', 'no'])->default('yes')->after('user_type');
            }

            if (!Schema::hasColumn('mpos', 'country_of_operation')) {
                $table->string('country_of_operation', 255)->nullable()->after('business_legal_name');
            }

            if (!Schema::hasColumn('mpos', 'agreement_type')) {
                $table->string('agreement_type', 255)->nullable()->after('country_of_operation');
            }

            if (!Schema::hasColumn('mpos', 'booking_registration_type')) {
                $table->string('booking_registration_type', 255)->nullable()->after('agreement_type');
            }

            if (!Schema::hasColumn('mpos', 'account_status')) {
                $table->enum('account_status', ['pending_verification', 'active', 'suspended', 'archived'])->default('pending_verification')->after('booking_registration_type');
            }

            if (!Schema::hasColumn('mpos', 'owner_full_name')) {
                $table->string('owner_full_name', 255)->nullable()->after('account_status');
            }

            if (!Schema::hasColumn('mpos', 'owner_email')) {
                $table->string('owner_email', 255)->nullable()->after('owner_full_name');
            }

            if (!Schema::hasColumn('mpos', 'owner_phone')) {
                $table->string('owner_phone', 20)->nullable()->after('owner_email');
            }

            if (!Schema::hasColumn('mpos', 'password_hash')) {
                $table->string('password_hash', 255)->after('owner_phone');
            }

            if (!Schema::hasColumn('mpos', 'password_reset_token')) {
                $table->string('password_reset_token', 255)->nullable()->after('password_hash');
            }

            if (!Schema::hasColumn('mpos', 'password_reset_expiry')) {
                $table->dateTime('password_reset_expiry')->nullable()->after('password_reset_token');
            }

            if (!Schema::hasColumn('mpos', 'business_id')) {
                $table->unsignedBigInteger('business_id')->nullable()->after('password_reset_expiry');
            }

            if (!Schema::hasColumn('mpos', 'operator_approve_flag')) {
                $table->tinyInteger('operator_approve_flag')->default(0)->after('business_id');
            }

            if (!Schema::hasColumn('mpos', 'registration_status')) {
                $table->enum('registration_status', ['in_progress', 'submitted', 'approved', 'rejected', 'under_review'])->default('in_progress')->after('operator_approve_flag');
            }

            if (!Schema::hasColumn('mpos', 'current_step')) {
                $table->integer('current_step')->default(1)->nullable()->after('registration_status');
            }

            if (!Schema::hasColumn('mpos', 'steps_completed')) {
                $table->json('steps_completed')->nullable()->after('current_step');
            }

            if (!Schema::hasColumn('mpos', 'submitted_at')) {
                $table->dateTime('submitted_at')->nullable()->after('steps_completed');
            }

            if (!Schema::hasColumn('mpos', 'approved_at')) {
                $table->dateTime('approved_at')->nullable()->after('submitted_at');
            }

            if (!Schema::hasColumn('mpos', 'approved_by')) {
                $table->integer('approved_by')->nullable()->after('approved_at');
            }

            if (!Schema::hasColumn('mpos', 'notes')) {
                $table->text('notes')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mpos', function (Blueprint $table) {
            foreach (['notes', 'approved_by', 'approved_at', 'submitted_at', 'steps_completed', 'current_step', 'registration_status', 'operator_approve_flag', 'business_id', 'password_reset_expiry', 'password_reset_token', 'password_hash', 'owner_phone', 'owner_email', 'owner_full_name', 'account_status', 'booking_registration_type', 'agreement_type', 'country_of_operation', 'is_owner', 'user_type'] as $column) {
                if (Schema::hasColumn('mpos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
