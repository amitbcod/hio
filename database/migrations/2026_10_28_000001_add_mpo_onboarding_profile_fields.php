<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_users', function (Blueprint $table): void {
            if (! Schema::hasColumn('admin_users', 'business_address_line2')) {
                $table->string('business_address_line2')->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'official_business_email')) {
                $table->string('official_business_email', 191)->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'emergency_mobile')) {
                $table->string('emergency_mobile', 20)->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'operating_timezone')) {
                $table->string('operating_timezone', 64)->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'supported_languages')) {
                $table->json('supported_languages')->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'brand_name')) {
                $table->string('brand_name', 191)->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'brand_description')) {
                $table->text('brand_description')->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'covered_region_ids')) {
                $table->json('covered_region_ids')->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'public_contact_email')) {
                $table->string('public_contact_email', 191)->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'public_contact_phone')) {
                $table->string('public_contact_phone', 30)->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'social_links')) {
                $table->json('social_links')->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'office_hours')) {
                $table->string('office_hours', 500)->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'website_url')) {
                $table->text('website_url')->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'mpo_password_setup_token_hash')) {
                $table->string('mpo_password_setup_token_hash', 64)->nullable();
            }
            if (! Schema::hasColumn('admin_users', 'mpo_password_setup_expires_at')) {
                $table->timestamp('mpo_password_setup_expires_at')->nullable();
            }
        });

        if (Schema::hasTable('regions') && ! Schema::hasColumn('regions', 'country_id')) {
            Schema::table('regions', function (Blueprint $table): void {
                $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('regions') && Schema::hasColumn('regions', 'country_id')) {
            Schema::table('regions', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('country_id');
            });
        }

        Schema::table('admin_users', function (Blueprint $table): void {
            $columns = [
                'mpo_password_setup_expires_at',
                'mpo_password_setup_token_hash',
                'website_url',
                'office_hours',
                'social_links',
                'public_contact_phone',
                'public_contact_email',
                'covered_region_ids',
                'brand_description',
                'brand_name',
                'supported_languages',
                'operating_timezone',
                'emergency_mobile',
                'official_business_email',
                'business_address_line2',
            ];
            $existing = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn('admin_users', $column)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
