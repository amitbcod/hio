<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ClosedGroupSettlementController;
use App\Http\Controllers\Frontend\BookingController;
use App\Models\BookingRef;
use App\Models\PaymentTransaction;
use App\Services\AdminGroupBookingSettlementService;
use App\Services\BookingOrderStatusSynchronizer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminGroupSettlementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'admin_group_settlement_test',
            'database.connections.admin_group_settlement_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('admin_group_settlement_test');
        DB::setDefaultConnection('admin_group_settlement_test');
        $this->createSchema();
        $this->seedAdminGroupOrder();
    }

    public function test_it_looks_up_the_admin_group_by_common_reference_and_uses_persisted_amounts(): void
    {
        $order = app(AdminGroupBookingSettlementService::class)->findEligibleOrder('BR-202-20261001-2885');

        $this->assertSame(2885.0, $order['total']);
        $this->assertCount(6, $order['items']);
        $this->assertSame(['Accommodation', 'Activity', 'Transport', 'Accommodation', 'Activity', 'Transport'], $order['items']->pluck('type')->all());
        $this->assertSame(0.0, $order['order_adjustment']);
        $this->assertSame('bank_transfer', $order['payment']->method);
        $this->assertSame('pending', $order['payment']->status);
        $this->assertSame('pending_verification', $order['payment']->settlement_status);
        $this->assertSame(2885.0, (float) $order['payment']->amount);
    }

    public function test_receipt_upload_stays_pending_until_explicit_verification(): void
    {
        Storage::fake('local');
        app('session.store')->put('admin_id', 7);
        $bookingRef = BookingRef::where('booking_ref_code', 'BR-202-20261001-2885')->firstOrFail();
        $request = Request::create('/admin/closed-groups/bank-transfer-settlement', 'POST', [
            'bank_transfer_date' => now()->toDateString(),
            'admin_notes' => 'Bank transfer confirmed by reference.',
        ], [], [
            'receipt' => UploadedFile::fake()->create('transfer.pdf', 32, 'application/pdf'),
        ]);
        $request->setLaravelSession(app('session.store'));

        $controller = new ClosedGroupSettlementController();
        $controller->submitProof($request, $bookingRef, app(AdminGroupBookingSettlementService::class));

        $payment = PaymentTransaction::where('booking_ref_id', $bookingRef->id)->firstOrFail();
        $this->assertSame('pending', $payment->status);
        $this->assertSame('pending_verification', $payment->settlement_status);
        $this->assertNotEmpty($payment->bank_transfer_receipt_path);
        Storage::disk('local')->assertExists($payment->bank_transfer_receipt_path);
        $this->assertServiceStatuses(1, 'Pending');

        $originalReceiptPath = $payment->bank_transfer_receipt_path;
        $duplicateRequest = Request::create('/admin/closed-groups/bank-transfer-settlement', 'POST', [
            'bank_transfer_date' => now()->toDateString(),
        ], [], [
            'receipt' => UploadedFile::fake()->create('replacement.pdf', 32, 'application/pdf'),
        ]);
        $duplicateRequest->setLaravelSession(app('session.store'));
        $controller->submitProof($duplicateRequest, $bookingRef, app(AdminGroupBookingSettlementService::class));

        $this->assertSame($originalReceiptPath, $payment->fresh()->bank_transfer_receipt_path);
        $this->assertSame(1, PaymentTransaction::where('booking_ref_id', $bookingRef->id)->count());

        $this->assertDatabaseHas('bookings', ['id' => 1, 'status' => 'pending']);
        $controller->verify(
            $bookingRef,
            app(AdminGroupBookingSettlementService::class),
            app(BookingOrderStatusSynchronizer::class)
        );

        $payment->refresh();
        $this->assertSame('paid', $payment->status);
        $this->assertSame('verified_settled', $payment->settlement_status);
        $this->assertSame(7, (int) $payment->verified_by);
        $this->assertNotNull($payment->verified_at);
        $this->assertDatabaseHas('bookings', ['id' => 1, 'status' => 'pending']);
        $this->assertServiceStatuses(1, 'Processing');
    }

    public function test_agency_success_processes_only_service_orders_for_the_payment_common_reference(): void
    {
        DB::table('booking_refs')->insert([
            'id' => 2,
            'trip_id' => 1,
            'booking_ref_code' => 'BR-FRONTEND-AGENCY',
            'total_amount' => 125,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('bookings')->insert([
            'id' => 2,
            'trip_id' => 1,
            'booking_ref_id' => 2,
            'total_amount' => 125,
            'status' => 'pending',
            'booking_type' => 'open-group',
            'is_admin_created' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('payment_transactions')->insert([
            'id' => 2,
            'booking_id' => 2,
            'booking_ref_id' => 2,
            'amount' => 125,
            'method' => 'againgency',
            'status' => 'pending',
            'transaction_ref' => 'agency-test-ref',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('accommodation_bookings')->insert([
            'id' => 3,
            'booking_ref_id' => 2,
            'accommodation_id' => 1,
            'room_id' => 1,
            'booking_reference' => 'ACC-AGENCY',
            'check_in_date' => '2026-10-01',
            'check_out_date' => '2026-10-02',
            'total_amount' => 25,
            'booking_status' => 'Pending',
        ]);
        DB::table('activity_bookings')->insert([
            'id' => 3,
            'booking_ref_id' => 2,
            'activity_id' => 1,
            'booking_reference' => 'ACT-AGENCY',
            'activity_date' => '2026-10-01',
            'total_amount' => 50,
            'booking_status' => 'Pending',
        ]);
        DB::table('transport_bookings')->insert([
            'id' => 3,
            'booking_ref_id' => 2,
            'transport_id' => 1,
            'booking_reference' => 'TRS-AGENCY',
            'pickup_date' => '2026-10-01',
            'total_amount' => 50,
            'booking_status' => 'Pending',
        ]);

        $response = (new BookingController())->paymentCallback(
            Request::create('/booking/payment/callback', 'POST', [
                'transaction_ref' => 'agency-test-ref',
                'status' => 'success',
            ]),
            app(BookingOrderStatusSynchronizer::class)
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertServiceStatuses(2, 'Processing');
        $this->assertServiceStatuses(1, 'Pending');

        DB::table('payment_transactions')->where('id', 2)->update(['status' => 'pending']);
        foreach (['accommodation_bookings', 'activity_bookings', 'transport_bookings'] as $table) {
            DB::table($table)->where('booking_ref_id', 2)->update(['booking_status' => 'Pending']);
        }

        (new BookingController())->paymentReturn(
            Request::create('/booking/payment/return', 'GET', [
                'transaction_ref' => 'agency-test-ref',
                'status' => 'success',
                'ref' => 'ACC-AGENCY',
            ]),
            app(BookingOrderStatusSynchronizer::class)
        );

        $this->assertSame('paid', DB::table('payment_transactions')->where('id', 2)->value('status'));
        $this->assertServiceStatuses(2, 'Processing');
        $this->assertServiceStatuses(1, 'Pending');
    }

    public function test_frontend_group_orders_are_not_eligible_for_admin_settlement(): void
    {
        DB::table('bookings')->insert([
            'id' => 2,
            'trip_id' => 1,
            'booking_ref_id' => 2,
            'total_amount' => 100,
            'status' => 'pending',
            'booking_type' => 'open-group',
            'is_admin_created' => false,
        ]);
        DB::table('booking_refs')->insert([
            'id' => 2,
            'trip_id' => 1,
            'booking_ref_code' => 'BR-FRONTEND-GROUP',
            'total_amount' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        app(AdminGroupBookingSettlementService::class)->findEligibleOrder('BR-FRONTEND-GROUP');
    }

    public function test_invoice_download_returns_a_pdf_for_the_common_reference(): void
    {
        $bookingRef = BookingRef::where('booking_ref_code', 'BR-202-20261001-2885')->firstOrFail();
        $response = (new ClosedGroupSettlementController())->downloadInvoice(
            $bookingRef,
            app(AdminGroupBookingSettlementService::class)
        );

        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('invoice-' . $bookingRef->id . '.pdf', $response->headers->get('Content-Disposition'));

        ob_start();
        $response->getCallback()();
        $pdf = ob_get_clean();
        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    private function createSchema(): void
    {
        Schema::create('trips', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('traveler_account_id')->nullable();
            $table->string('title')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        Schema::create('travellers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('relationship')->nullable();
            $table->timestamps();
        });
        Schema::create('groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
        });
        Schema::create('booking_refs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->string('booking_ref_code')->unique();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->timestamps();
        });
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('booking_ref_id')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->nullable();
            $table->string('booking_type')->nullable();
            $table->boolean('is_admin_created')->default(false);
            $table->timestamps();
        });
        Schema::create('booking_line_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('service_type');
            $table->unsignedBigInteger('service_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('booking_ref_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('method');
            $table->string('status');
            $table->string('transaction_ref')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('settlement_status')->nullable();
            $table->date('bank_transfer_date')->nullable();
            $table->string('bank_transfer_receipt_path')->nullable();
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
        Schema::create('accommodations', function (Blueprint $table): void {
            $table->id();
            $table->string('property_name')->nullable();
            $table->string('name')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
        Schema::create('accommodation_rooms', function (Blueprint $table): void {
            $table->id();
            $table->string('room_name')->nullable();
            $table->string('room_type')->nullable();
        });
        Schema::create('accommodation_bookings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('booking_ref_id');
            $table->unsignedBigInteger('accommodation_id');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->string('booking_reference');
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->unsignedInteger('rooms_booked')->default(1);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('booking_status')->nullable();
            $table->unsignedBigInteger('guest_otp_token_id')->nullable();
            $table->timestamps();
        });
        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $table->string('activity_name')->nullable();
            $table->string('name')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
        Schema::create('activity_bookings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('booking_ref_id');
            $table->unsignedBigInteger('activity_id');
            $table->string('booking_reference');
            $table->string('variant_name')->nullable();
            $table->date('activity_date')->nullable();
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('booking_status')->nullable();
            $table->unsignedBigInteger('guest_otp_token_id')->nullable();
            $table->timestamps();
        });
        Schema::create('transports', function (Blueprint $table): void {
            $table->id();
            $table->string('vehicle_display_name')->nullable();
            $table->string('vehicle_name')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
        Schema::create('transport_bookings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('booking_ref_id');
            $table->unsignedBigInteger('transport_id');
            $table->string('booking_reference');
            $table->date('pickup_date')->nullable();
            $table->date('return_date')->nullable();
            $table->string('route_from')->nullable();
            $table->string('route_to')->nullable();
            $table->unsignedInteger('total_passengers')->default(1);
            $table->unsignedInteger('adults')->default(1);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('booking_status')->nullable();
            $table->timestamps();
        });
    }

    private function seedAdminGroupOrder(): void
    {
        DB::table('trips')->insert(['id' => 1, 'title' => 'Mauritius Luxury Group', 'status' => 'planned', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('travellers')->insert(['trip_id' => 1, 'name' => 'Lead Traveller', 'email' => 'lead@example.test', 'relationship' => 'lead', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('groups')->insert(['id' => 1, 'name' => 'Mauritius Luxury Group']);
        DB::table('booking_refs')->insert(['id' => 1, 'trip_id' => 1, 'booking_ref_code' => 'BR-202-20261001-2885', 'total_amount' => 2885, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('bookings')->insert(['id' => 1, 'trip_id' => 1, 'booking_ref_id' => 1, 'total_amount' => 2885, 'status' => 'pending', 'booking_type' => 'open-group', 'is_admin_created' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('booking_line_items')->insert(['booking_id' => 1, 'service_type' => 'package', 'service_id' => 1, 'quantity' => 1, 'price' => 2885, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);

        DB::table('accommodations')->insert(['id' => 1, 'property_name' => 'Amit Resort']);
        DB::table('accommodation_rooms')->insert([['id' => 1, 'room_name' => 'Standard'], ['id' => 2, 'room_name' => 'Apartment']]);
        DB::table('accommodation_bookings')->insert([
            ['booking_ref_id' => 1, 'accommodation_id' => 1, 'room_id' => 1, 'booking_reference' => 'ACC-1', 'check_in_date' => '2026-10-01', 'check_out_date' => '2026-10-02', 'total_amount' => 500, 'booking_status' => 'Pending'],
            ['booking_ref_id' => 1, 'accommodation_id' => 1, 'room_id' => 2, 'booking_reference' => 'ACC-2', 'check_in_date' => '2026-10-02', 'check_out_date' => '2026-10-03', 'total_amount' => 1000, 'booking_status' => 'Pending'],
        ]);
        DB::table('activities')->insert([['id' => 1, 'activity_name' => 'Tracing 4'], ['id' => 2, 'activity_name' => 'Tracing 3']]);
        DB::table('activity_bookings')->insert([
            ['booking_ref_id' => 1, 'activity_id' => 1, 'booking_reference' => 'ACT-1', 'variant_name' => 'Tracing 4 | Per Person', 'activity_date' => '2026-10-01', 'adults' => 2, 'total_amount' => 360, 'booking_status' => 'Pending'],
            ['booking_ref_id' => 1, 'activity_id' => 2, 'booking_reference' => 'ACT-2', 'variant_name' => 'Tracing 3 | Per Person', 'activity_date' => '2026-10-02', 'adults' => 2, 'total_amount' => 900, 'booking_status' => 'Pending'],
        ]);
        DB::table('transports')->insert([['id' => 1, 'vehicle_display_name' => 'Coaster'], ['id' => 2, 'vehicle_display_name' => 'Van']]);
        DB::table('transport_bookings')->insert([
            ['booking_ref_id' => 1, 'transport_id' => 1, 'booking_reference' => 'TRS-1', 'pickup_date' => '2026-10-01', 'route_from' => 'Airport', 'route_to' => 'South East', 'total_passengers' => 2, 'total_amount' => 75, 'booking_status' => 'Pending'],
            ['booking_ref_id' => 1, 'transport_id' => 2, 'booking_reference' => 'TRS-2', 'pickup_date' => '2026-10-02', 'route_from' => 'Airport', 'route_to' => 'South East', 'total_passengers' => 2, 'total_amount' => 50, 'booking_status' => 'Pending'],
        ]);
    }

    private function assertServiceStatuses(int $bookingReferenceId, string $status): void
    {
        foreach (['accommodation_bookings', 'activity_bookings', 'transport_bookings'] as $table) {
            $this->assertSame(
                0,
                DB::table($table)->where('booking_ref_id', $bookingReferenceId)->where('booking_status', '!=', $status)->count(),
                "Unexpected {$table} status for booking reference {$bookingReferenceId}."
            );
        }
    }
}