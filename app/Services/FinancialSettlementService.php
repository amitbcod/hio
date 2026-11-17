<?php

namespace App\Services;

use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\AdminUser;
use App\Models\FinancialBookingReview;
use App\Models\FinancialSettlement;
use App\Models\FinancialSettlementEvent;
use App\Models\FinancialSettlementItem;
use App\Models\Operator;
use App\Models\OperatorAccountingPayout;
use App\Models\OperatorCollaborationAgreement;
use App\Models\TransportBooking;
use App\Support\FinancialAmount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FinancialSettlementService
{
    private const HIO_SLA = 'hio_sla';
    private const OPERATOR_PAYOUT = 'operator_payout';

    public function __construct(private readonly OperatorCommissionCalculator $commissionCalculator) {}

    public function generateForMpo(AdminUser $mpo, ?object $actor = null): array
    {
        abort_unless($mpo->admin_type === 'mpo' && $mpo->country_id, 403);
        $bookings = $this->eligibleBookings((int) $mpo->country_id);
        $created = ['hio_sla' => 0, 'operator_payout' => 0, 'review_required' => 0];

        foreach ($bookings->filter(fn (array $booking): bool => $booking['currency'] !== 'USD') as $booking) {
            $this->flagReview($mpo, $booking, 'unsupported_currency', 'Financial settlements are USD-only; no settlement was generated for this booking.');
            $created['review_required']++;
        }
        foreach ($bookings->filter(fn (array $booking): bool => $booking['currency'] === 'USD'
            && FinancialAmount::cents($booking['gross_amount']) <= 0) as $booking) {
            $this->flagReview($mpo, $booking, 'invalid_gross_amount', 'The booking gross must be greater than zero before a financial settlement can be generated.');
            $created['review_required']++;
        }
        $created['hio_sla'] = $this->createSettlements($mpo, $bookings, self::HIO_SLA, $actor);
        $operatorBookings = $bookings->filter(fn (array $booking): bool => $booking['currency'] === 'USD');
        foreach ($operatorBookings as $booking) {
            if ($booking['operator_schedule'] === 'On Request') {
                continue;
            }
            if ($booking['agreement'] === null) {
                $this->flagReview($mpo, $booking, 'missing_agreement', 'No active Operator Collaboration Agreement applies to this booking.');
                $created['review_required']++;
                continue;
            }
            if ($booking['commission_amount'] === null) {
                $this->flagReview($mpo, $booking, 'unsupported_commission', 'The applicable agreement does not have a supported percentage commission rule.');
                $created['review_required']++;
                continue;
            }
            $hioSla = FinancialAmount::percentage($booking['gross_amount'], config('financial_settlements.hio_sla_percentage'));
            if (FinancialAmount::cents($booking['commission_amount']) + FinancialAmount::cents($hioSla)
                > FinancialAmount::cents($booking['gross_amount'])) {
                $this->flagReview($mpo, $booking, 'invalid_payout_amount', 'The agreement commission and HIO SLA exceed the booking gross; payout requires authorised review.');
                $created['review_required']++;
                continue;
            }
            if ($booking['operator_schedule'] === 'Unavailable') {
                $this->flagReview($mpo, $booking, 'missing_payout_schedule', 'The Operator has no supported Step 7 payout schedule.');
                $created['review_required']++;
                continue;
            }
        }
        $created['operator_payout'] = $this->createSettlements(
            $mpo,
            $operatorBookings->filter(fn (array $booking): bool => $booking['operator_schedule'] !== 'On Request'
                && $this->canCreateOperatorPayout($booking)),
            self::OPERATOR_PAYOUT,
            $actor,
        );

        return $created;
    }

    public function requestOperatorPayout(Operator $operator, object $actor): int
    {
        $countryId = (int) $operator->country_id;
        $mpo = AdminUser::query()
            ->where('admin_type', 'mpo')
            ->where('country_id', $countryId)
            ->firstOrFail();
        $bookings = $this->eligibleBookings($countryId)
            ->filter(fn (array $booking): bool => (int) $booking['operator']->id === (int) $operator->id
                && $booking['operator_schedule'] === 'On Request');
        foreach ($bookings as $booking) {
            if ($booking['currency'] !== 'USD') {
                $this->flagReview($mpo, $booking, 'unsupported_currency', 'Financial settlements are USD-only; no settlement was generated for this booking.');
            } elseif (FinancialAmount::cents($booking['gross_amount']) <= 0) {
                $this->flagReview($mpo, $booking, 'invalid_gross_amount', 'The booking gross must be greater than zero before a financial settlement can be generated.');
            } elseif ($booking['agreement'] === null) {
                $this->flagReview($mpo, $booking, 'missing_agreement', 'No active Operator Collaboration Agreement applies to this booking.');
            } elseif ($booking['commission_amount'] === null) {
                $this->flagReview($mpo, $booking, 'unsupported_commission', 'The applicable agreement has a missing or invalid commission percentage.');
            } elseif (! $this->canCreateOperatorPayout($booking)) {
                $this->flagReview($mpo, $booking, 'invalid_payout_amount', 'The agreement commission and HIO SLA exceed the booking gross; payout requires authorised review.');
            }
        }
        $bookings = $bookings->filter(fn (array $booking): bool => $this->canCreateOperatorPayout($booking));

        return $this->createSettlements($mpo, $bookings, self::OPERATOR_PAYOUT, $actor, true);
    }

    public function recordTransfer(
        FinancialSettlement $settlement,
        array $data,
        string $actorType,
        int $actorId,
    ): void {
        DB::transaction(function () use ($settlement, $data, $actorType, $actorId): void {
            $locked = FinancialSettlement::query()->lockForUpdate()->findOrFail($settlement->id);
            $allowedStatuses = $locked->settlement_type === self::HIO_SLA
                ? ['Payable', 'Partially Paid', 'Awaiting Verification']
                : ['Authorised', 'Partially Paid', 'Transfer Initiated'];
            abort_unless(in_array($locked->status, $allowedStatuses, true), 409, 'This settlement must be invoiced or authorised before recording a transfer.');
            $receivedCents = FinancialAmount::cents(FinancialAmount::sum($locked->transfers()->pluck('amount')));
            $amountCents = FinancialAmount::cents($data['amount']);
            abort_if($amountCents <= 0 || $receivedCents + $amountCents > FinancialAmount::cents($locked->net_amount), 422, 'Transfer amount exceeds the remaining settlement balance.');

            $transferSequence = (int) $locked->transfers()->count() + 1;
            $transfer = $locked->transfers()->create([
                'transfer_reference' => strtoupper(Str::random(4)).'-'.now()->format('Ymd').'-'.str_pad((string) $transferSequence, 4, '0', STR_PAD_LEFT),
                'transaction_number' => $data['transaction_number'],
                'transferred_on' => $data['transferred_on'],
                'amount' => FinancialAmount::decimal($amountCents),
                'currency' => $locked->currency,
                'status' => 'Recorded',
                'evidence_path' => $data['evidence_path'],
                'notes' => $data['notes'] ?? null,
                'recorded_by_type' => $actorType,
                'recorded_by_id' => $actorId,
            ]);

            $from = $locked->status;
            $locked->status = $locked->settlement_type === self::HIO_SLA ? 'Awaiting Verification' : 'Transfer Initiated';
            $locked->saveOrFail();
            $this->event($locked, 'transfer_recorded', $from, $locked->status, $actorType, $actorId, $data['notes'] ?? null, $transfer->id);
        });
    }

    public function authorizeOperatorPayout(FinancialSettlement $settlement, string $actorType, int $actorId): void
    {
        DB::transaction(function () use ($settlement, $actorType, $actorId): void {
            $locked = FinancialSettlement::query()->lockForUpdate()->findOrFail($settlement->id);
            abort_unless(
                $locked->settlement_type === self::OPERATOR_PAYOUT
                    && in_array($locked->status, ['Awaiting Authorisation', 'Requested'], true),
                409,
                'Only a pending Operator payout can be authorized.',
            );
            $from = $locked->status;
            $locked->status = 'Authorised';
            $locked->saveOrFail();
            $this->event($locked, 'operator_payout_authorized', $from, $locked->status, $actorType, $actorId);
        });
    }

    public function issueHioInvoice(
        FinancialSettlement $settlement,
        string $invoiceReference,
        string $issuedOn,
        string $dueOn,
        string $actorType,
        int $actorId,
    ): void {
        DB::transaction(function () use ($settlement, $invoiceReference, $issuedOn, $dueOn, $actorType, $actorId): void {
            $locked = FinancialSettlement::query()->lockForUpdate()->findOrFail($settlement->id);
            abort_unless($locked->settlement_type === self::HIO_SLA && $locked->status === 'Statement Generated', 409, 'Only an uninvoiced HIO SLA statement can be invoiced.');
            $from = $locked->status;
            $locked->invoice_reference = $invoiceReference;
            $locked->invoice_issued_on = $issuedOn;
            $locked->invoice_due_on = $dueOn;
            $locked->status = 'Payable';
            $locked->saveOrFail();
            $this->event($locked, 'hio_invoice_issued', $from, $locked->status, $actorType, $actorId, $invoiceReference);
        });
    }

    public function acknowledgeHioReceipt(FinancialSettlement $settlement, string $actorType, int $actorId): void
    {
        DB::transaction(function () use ($settlement, $actorType, $actorId): void {
            $locked = FinancialSettlement::query()->lockForUpdate()->findOrFail($settlement->id);
            abort_unless($locked->settlement_type === self::HIO_SLA && $locked->status === 'Received', 409, 'The HIO receipt must be verified before acknowledgment.');
            $from = $locked->status;
            $locked->status = 'Acknowledged';
            $locked->saveOrFail();
            $this->event($locked, 'hio_receipt_acknowledged', $from, $locked->status, $actorType, $actorId);
        });
    }

    public function verifyTransfer(
        FinancialSettlement $settlement,
        int $transferId,
        string $actorType,
        int $actorId,
    ): void {
        DB::transaction(function () use ($settlement, $transferId, $actorType, $actorId): void {
            $locked = FinancialSettlement::query()->lockForUpdate()->findOrFail($settlement->id);
            $transfer = $locked->transfers()->lockForUpdate()->findOrFail($transferId);
            abort_unless($transfer->status === 'Recorded', 409, 'This transfer has already been verified.');
            $transfer->status = 'Verified';
            $transfer->verified_by_type = $actorType;
            $transfer->verified_by_id = $actorId;
            $transfer->verified_at = now();
            $transfer->saveOrFail();

            $verifiedCents = FinancialAmount::cents(FinancialAmount::sum(
                $locked->transfers()->where('status', 'Verified')->pluck('amount'),
            ));
            $from = $locked->status;
            if ($verifiedCents >= FinancialAmount::cents($locked->net_amount)) {
                $locked->status = $locked->settlement_type === self::HIO_SLA ? 'Received' : 'Paid';
            } elseif ($verifiedCents > 0) {
                $locked->status = 'Partially Paid';
            }
            $locked->saveOrFail();
            $this->event($locked, 'transfer_verified', $from, $locked->status, $actorType, $actorId, null, $transfer->id);
        });
    }

    public function acknowledgeOperatorPayment(FinancialSettlement $settlement, string $actorType, int $actorId): void
    {
        abort_unless($settlement->settlement_type === self::OPERATOR_PAYOUT && $settlement->status === 'Paid', 409);

        DB::transaction(function () use ($settlement, $actorType, $actorId): void {
            $locked = FinancialSettlement::query()->lockForUpdate()->findOrFail($settlement->id);
            abort_unless($locked->status === 'Paid', 409);
            $from = $locked->status;
            $locked->status = 'Acknowledged';
            $locked->saveOrFail();
            $this->event($locked, 'operator_acknowledged', $from, $locked->status, $actorType, $actorId);
        });
    }

    public function eligibleBookings(int $countryId): Collection
    {
        $delay = max(0, (int) config('financial_settlements.eligibility_delay_days', 14));
        $cutoff = CarbonImmutable::today();
        $bookings = collect();

            $definitions = [
                'accommodation' => [
                    'model' => AccommodationBooking::class,
                    'relation' => 'accommodation',
                    'date' => fn (Model $booking) => $booking->check_out_date,
                ],
                'activity' => [
                    'model' => ActivityBooking::class,
                    'relation' => 'activity',
                    'date' => fn (Model $booking) => $booking->activity_date,
                ],
                'transport' => [
                    'model' => TransportBooking::class,
                    'relation' => 'transport',
                    'date' => fn (Model $booking) => $booking->return_date ?: $booking->pickup_date,
                ],
            ];

        foreach ($definitions as $serviceType => $definition) {
            $model = $definition['model'];
            $query = $model::query()
                ->withoutGlobalScope('admin_country')
                ->paymentComplete()
                ->whereHas($definition['relation'].'.operator', fn ($query) => $query
                    ->withoutGlobalScope('admin_country')
                    ->where('country_id', $countryId))
                ->with([
                    'bookingRef.trip',
                    $definition['relation'].'.operator' => fn ($query) => $query
                        ->withoutGlobalScope('admin_country')
                        ->with('business'),
                ])
                ;
            if ($serviceType === 'transport') {
                $query->whereRaw('LOWER(booking_status) = ?', ['completed']);
            } else {
                $query->whereNotIn(DB::raw('LOWER(booking_status)'), ['pending', 'cancelled']);
            }
            $records = $query->get();

            foreach ($records as $record) {
                $serviceDate = $definition['date']($record);
                if (! $serviceDate) {
                    continue;
                }
                $serviceDate = CarbonImmutable::parse($serviceDate)->startOfDay();
                $eligibleDate = $serviceDate->addDays($delay);
                if ($eligibleDate->greaterThan($cutoff)) {
                    continue;
                }

                $service = $record->{$definition['relation']};
                $operator = $service?->operator;
                $mpo = AdminUser::query()->where('admin_type', 'mpo')
                    ->where('country_id', $countryId)->first();
                if (! $operator || ! $mpo) {
                    continue;
                }

                $currency = strtoupper((string) ($record->currency ?: ''));
                $agreement = $this->applicableOperatorAgreement($operator, $serviceDate);
                $commission = $this->commissionCalculator->calculate($agreement, (string) $record->total_amount);

                $bookings->push([
                    'service_type' => $serviceType,
                    'booking' => $record,
                    'service_date' => $serviceDate,
                    'eligible_date' => $eligibleDate,
                    'country_id' => $countryId,
                    'mpo' => $mpo,
                    'operator' => $operator,
                    'currency' => $currency,
                    'gross_amount' => (string) $record->total_amount,
                    'agreement' => $agreement,
                    'commission_type' => $commission['type'] ?? null,
                    'commission_value' => $commission['value'] ?? null,
                    'commission_amount' => $commission['amount'] ?? null,
                    'operator_schedule' => $this->operatorSchedule($operator),
                ]);
            }
        }

        return $bookings;
    }

    private function applicableOperatorAgreement(Operator $operator, CarbonImmutable $serviceDate): ?OperatorCollaborationAgreement
    {
        if (! Schema::hasTable('operator_collaboration_agreements')) {
            return null;
        }
        $query = OperatorCollaborationAgreement::query()->withoutGlobalScope('admin_country')
            ->where('status', 'Active')
            ->whereDate('start_date', '<=', $serviceDate->toDateString())
            ->where(function ($endDate) use ($serviceDate): void {
                $endDate->whereNull('end_date')->orWhereDate('end_date', '>=', $serviceDate->toDateString());
            });
        if (Schema::hasColumn('operator_collaboration_agreements', 'business_id') && $operator->business_id) {
            $query->where(function ($owner) use ($operator): void {
                $owner->where('business_id', $operator->business_id)
                    ->orWhere('operator_id', $operator->operator_id);
            });
        } else {
            $query->where('operator_id', $operator->operator_id);
        }

        return $query->latest('id')->first();
    }

    private function operatorSchedule(Operator $operator): string
    {
        $query = OperatorAccountingPayout::query()->withoutGlobalScope('admin_country');
        $accounting = $operator->business_id
            ? $query->where('business_id', $operator->business_id)->first()
            : $query->where('beneficiary_id', $operator->operator_id)->first();

        return $accounting?->status === 'active'
            && in_array($accounting->payment_schedule, ['Monthly', 'Quarterly', 'Service Provided', 'On Request'], true)
            ? $accounting->payment_schedule
            : 'Unavailable';
    }

    private function canCreateOperatorPayout(array $booking): bool
    {
        if ($booking['currency'] !== 'USD'
            || $booking['agreement'] === null
            || $booking['commission_amount'] === null
            || $booking['operator_schedule'] === 'Unavailable'
            || FinancialAmount::cents($booking['gross_amount']) <= 0) {
            return false;
        }

        $hioSla = FinancialAmount::percentage($booking['gross_amount'], config('financial_settlements.hio_sla_percentage'));

        return FinancialAmount::cents($booking['commission_amount']) + FinancialAmount::cents($hioSla)
            <= FinancialAmount::cents($booking['gross_amount']);
    }

    private function createSettlements(
        AdminUser $mpo,
        Collection $bookings,
        string $type,
        ?object $actor,
        bool $requested = false,
    ): int {
        $groups = $bookings
            ->filter(fn (array $booking): bool => $booking['currency'] === 'USD')
            ->filter(fn (array $booking): bool => FinancialAmount::cents($booking['gross_amount']) > 0)
            ->filter(function (array $booking) use ($type, $requested): bool {
                $schedule = $type === self::HIO_SLA ? 'Monthly' : $booking['operator_schedule'];
                if ($requested || ! in_array($schedule, ['Monthly', 'Quarterly'], true)) {
                    return true;
                }

                [, $periodEnd] = $this->period($schedule, $booking['eligible_date'], false);

                return CarbonImmutable::parse($periodEnd)->lessThanOrEqualTo(CarbonImmutable::today());
            })
            ->filter(fn (array $booking): bool => ! FinancialSettlementItem::query()
                ->where('settlement_type', $type)
                ->where('service_type', $booking['service_type'])
                ->where('service_booking_id', $booking['booking']->id)
                ->exists())
            ->groupBy(function (array $booking) use ($type, $requested): string {
                $schedule = $type === self::HIO_SLA ? 'Monthly' : $booking['operator_schedule'];
                [$periodStart, $periodEnd] = $this->period($schedule, $booking['eligible_date'], $requested);
                $operatorKey = $type === self::OPERATOR_PAYOUT ? (string) $booking['operator']->id : 'hio';

                return implode('|', [$operatorKey, $schedule, $periodStart, $periodEnd]);
            });
        $created = 0;

        foreach ($groups as $group) {
            $first = $group->first();
            $schedule = $type === self::HIO_SLA ? 'Monthly' : $first['operator_schedule'];
            [$periodStart, $periodEnd] = $this->period($schedule, $first['eligible_date'], $requested);
            $operator = $type === self::OPERATOR_PAYOUT ? $first['operator'] : null;
            $key = hash('sha256', implode('|', [$type, $mpo->id, $operator?->id ?? 0, $schedule, $periodStart, $periodEnd]));

            DB::transaction(function () use ($mpo, $actor, $type, $group, $operator, $schedule, $periodStart, $periodEnd, $key, $requested, &$created): void {
                $settlement = FinancialSettlement::query()->firstOrCreate(
                    ['generated_key' => $key],
                    [
                        'reference' => $type === self::HIO_SLA
                            ? 'HIO-'.str_pad((string) $mpo->id, 5, '0', STR_PAD_LEFT).'-'.str_replace('-', '', $periodStart)
                            : 'OP-'.str_pad((string) $operator->id, 6, '0', STR_PAD_LEFT).'-'.str_replace('-', '', $periodStart).'-'.substr($key, 0, 5),
                        'settlement_type' => $type,
                        'country_id' => $mpo->country_id,
                        'mpo_admin_user_id' => $mpo->id,
                        'operator_id' => $operator?->id,
                        'payment_schedule' => $schedule,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'currency' => 'USD',
                        'status' => $type === self::HIO_SLA
                            ? 'Statement Generated'
                            : ($requested || $schedule === 'On Request' ? 'Requested' : 'Awaiting Authorisation'),
                        'created_by_type' => $actor ? class_basename($actor) : 'system',
                        'created_by_id' => $actor?->id,
                        'configuration_snapshot' => $this->configurationSnapshot($mpo, $operator, $type, $schedule),
                    ],
                );

                foreach ($group as $data) {
                    if (FinancialSettlementItem::query()
                        ->where('settlement_type', $type)
                        ->where('service_type', $data['service_type'])
                        ->where('service_booking_id', $data['booking']->id)
                        ->exists()) {
                        continue;
                    }

                    $gross = FinancialAmount::decimal(FinancialAmount::cents($data['gross_amount']));
                    $operatorCommission = $type === self::OPERATOR_PAYOUT ? $data['commission_amount'] : '0.00';
                    $sla = FinancialAmount::percentage($gross, config('financial_settlements.hio_sla_percentage'));
                    $net = $type === self::HIO_SLA
                        ? $sla
                        : FinancialAmount::decimal(
                            FinancialAmount::cents($gross)
                            - FinancialAmount::cents($operatorCommission)
                            - FinancialAmount::cents($sla),
                        );

                    $item = $settlement->items()->create([
                        'settlement_type' => $type,
                        'service_type' => $data['service_type'],
                        'service_booking_id' => $data['booking']->id,
                        'booking_reference' => $data['booking']->booking_reference,
                        'booking_ref_id' => $data['booking']->booking_ref_id,
                        'trip_id' => $data['booking']->bookingRef?->trip_id ?? $data['booking']->trip_id ?? null,
                        'operator_id' => $data['operator']->id,
                        'service_date' => $data['service_date']->toDateString(),
                        'eligible_date' => $data['eligible_date']->toDateString(),
                        'currency' => 'USD',
                        'gross_amount' => $gross,
                        'agreement_reference' => $type === self::OPERATOR_PAYOUT ? $data['agreement']?->agreement_id : null,
                        'agreement_type' => $type === self::OPERATOR_PAYOUT ? $data['agreement']?->agreement_type : null,
                        'commission_type' => $type === self::OPERATOR_PAYOUT ? $data['commission_type'] : null,
                        'commission_value' => $type === self::OPERATOR_PAYOUT ? $data['commission_value'] : null,
                        'commission_base_amount' => $type === self::OPERATOR_PAYOUT ? $gross : null,
                        'operator_commission_amount' => $type === self::OPERATOR_PAYOUT ? $operatorCommission : null,
                        'hio_sla_rate' => config('financial_settlements.hio_sla_percentage'),
                        'hio_sla_amount' => $sla,
                        'net_amount' => $net,
                        'calculation_snapshot' => [
                            'calculated_at' => now()->toIso8601String(),
                            'gross_amount' => $gross,
                            'operator_commission_formula' => $type === self::OPERATOR_PAYOUT ? 'booking gross × Operator Step 5 agreement percentage' : null,
                            'operator_commission_amount' => $type === self::OPERATOR_PAYOUT ? $operatorCommission : null,
                            'hio_sla_formula' => 'USD eligible gross × 0.5000%',
                            'hio_sla_amount' => $sla,
                            'net_operator_payable' => $type === self::OPERATOR_PAYOUT ? $net : null,
                            'agreement' => $type === self::OPERATOR_PAYOUT ? [
                                'id' => $data['agreement']?->id,
                                'reference' => $data['agreement']?->agreement_id,
                                'type' => $data['agreement']?->agreement_type,
                                'commission_type' => $data['commission_type'],
                                'commission_value' => $data['commission_value'],
                            ] : null,
                        ],
                    ]);

                    $settlement->gross_amount = FinancialAmount::sum([$settlement->gross_amount, $item->gross_amount]);
                    $settlement->operator_commission_amount = FinancialAmount::sum([$settlement->operator_commission_amount, $item->operator_commission_amount ?? '0']);
                    $settlement->hio_sla_amount = FinancialAmount::sum([$settlement->hio_sla_amount, $item->hio_sla_amount]);
                    $settlement->net_amount = FinancialAmount::sum([$settlement->net_amount, $item->net_amount]);
                    if ($type === self::OPERATOR_PAYOUT) {
                        FinancialBookingReview::query()
                            ->where('service_type', $data['service_type'])
                            ->where('service_booking_id', $data['booking']->id)
                            ->where('status', 'Open')
                            ->update([
                                'status' => 'Resolved',
                                'reviewed_by_type' => $actor ? class_basename($actor) : 'system',
                                'reviewed_by_id' => $actor?->id,
                                'reviewed_at' => now(),
                            ]);
                    }
                    $created++;
                }

                $settlement->saveOrFail();
                if ($settlement->wasRecentlyCreated) {
                    $this->event(
                        $settlement,
                        'settlement_generated',
                        null,
                        $settlement->status,
                        $actor ? class_basename($actor) : 'system',
                        (int) ($actor?->id ?? 0),
                    );
                }
            });
        }

        return $created;
    }

    private function period(string $schedule, CarbonImmutable $eligibleDate, bool $requested): array
    {
        if ($requested || $schedule === 'On Request') {
            return [$eligibleDate->startOfMonth()->toDateString(), $eligibleDate->endOfMonth()->toDateString()];
        }
        if ($schedule === 'Monthly') {
            return [$eligibleDate->startOfMonth()->toDateString(), $eligibleDate->endOfMonth()->toDateString()];
        }
        if ($schedule === 'Quarterly') {
            $quarterStart = $eligibleDate->startOfQuarter();

            return [$quarterStart->toDateString(), $quarterStart->endOfQuarter()->toDateString()];
        }

        return [$eligibleDate->toDateString(), $eligibleDate->toDateString()];
    }

    private function configurationSnapshot(AdminUser $mpo, ?Operator $operator, string $type, string $schedule): array
    {
        if ($type === self::HIO_SLA) {
            return [
                'configuration' => 'HIO Global SLA V1',
                'rate' => config('financial_settlements.hio_sla_percentage'),
                'schedule' => 'Monthly',
                'currency' => 'USD',
            ];
        }

        $query = OperatorAccountingPayout::query()->withoutGlobalScope('admin_country');
        $accounting = $operator?->business_id
            ? $query->where('business_id', $operator->business_id)->first()
            : $query->where('beneficiary_id', $operator?->operator_id)->first();

        return [
            'operator_id' => $operator?->id,
            'payment_schedule' => $schedule,
            'currency_preference' => $accounting?->currency_preference,
            'credit_limit_days' => $accounting?->credit_limit_days,
            'credit_limit_amount' => $accounting?->credit_limit_amount,
            'credit_value' => $accounting?->credit_value,
        ];
    }

    private function flagReview(AdminUser $mpo, array $booking, string $reasonCode, string $reason): void
    {
        FinancialBookingReview::query()->updateOrCreate(
            [
                'service_type' => $booking['service_type'],
                'service_booking_id' => $booking['booking']->id,
            ],
            [
                'country_id' => $mpo->country_id,
                'mpo_admin_user_id' => $mpo->id,
                'operator_id' => $booking['operator']->id,
                'booking_reference' => $booking['booking']->booking_reference,
                'reason_code' => $reasonCode,
                'reason' => $reason,
                'status' => 'Open',
            ],
        );
    }

    private function event(
        FinancialSettlement $settlement,
        string $eventType,
        ?string $from,
        ?string $to,
        string $actorType,
        int $actorId,
        ?string $remarks = null,
        ?int $transferId = null,
    ): void {
        FinancialSettlementEvent::query()->create([
            'financial_settlement_id' => $settlement->id,
            'transfer_id' => $transferId,
            'event_type' => $eventType,
            'from_status' => $from,
            'to_status' => $to,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'remarks' => $remarks,
        ]);
    }
}
