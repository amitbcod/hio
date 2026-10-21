<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Admin\ClosedGroupBookingController as AdminClosedGroupBookingController;
use App\Http\Controllers\Controller;
use App\Models\AgentAccount;
use App\Models\AgentStaffUser;
use App\Models\Booking;
use App\Models\BookingRef;
use App\Models\Group;
use App\Models\PaymentTransaction;
use App\Support\ClosedGroupBookingDates;
use App\Support\CountryOwnership;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClosedGroupBookingController extends Controller
{
    public function index(Request $request)
    {
        $agent = $this->agentFor($request);

        $bookings = $agent->bookings()
            ->with(['bookingRef', 'trip', 'lineItems', 'payments'])
            ->where('booking_type', 'open-group')
            ->latest()
            ->paginate(15);

        return view('agent.closed_groups.index', compact('agent', 'bookings'));
    }

    public function show(Request $request, int $booking)
    {
        $agent = $this->agentFor($request);
        $booking = $agent->bookings()
            ->with(['bookingRef', 'trip.travellers', 'lineItems', 'payments'])
            ->where('booking_type', 'open-group')
            ->findOrFail($booking);

        return view('agent.closed_groups.show', compact('agent', 'booking'));
    }

    public function submitBankTransferProof(Request $request, int $booking)
    {
        $agent = $this->agentFor($request);
        $booking = $agent->bookings()
            ->where('booking_type', 'open-group')
            ->findOrFail($booking);

        $data = $request->validate([
            'bank_transfer_date' => 'required|date|before_or_equal:today',
            'receipt' => 'required|file|mimes:pdf,png,jpg,jpeg|max:5120',
        ]);

        $bookingRef = $booking->bookingRef;
        abort_unless($bookingRef instanceof BookingRef, 404);
        $payment = PaymentTransaction::query()
            ->where('booking_id', $booking->id)
            ->where('booking_ref_id', $bookingRef->id)
            ->firstOrFail();

        if ($payment->status === 'paid' || $payment->settlement_status === 'verified_settled') {
            throw ValidationException::withMessages([
                'receipt' => 'This group booking has already been verified and settled.',
            ]);
        }

        if ($payment->bank_transfer_receipt_path) {
            return redirect()->route('agent.closed-groups.show', $booking->id)
                ->with('status', 'Bank-transfer proof has already been submitted and is awaiting Admin verification.');
        }

        $storedPath = null;
        try {
            DB::transaction(function () use ($request, $data, $payment, &$storedPath): void {
                $lockedPayment = PaymentTransaction::whereKey($payment->id)->lockForUpdate()->firstOrFail();
                if ($lockedPayment->bank_transfer_receipt_path) {
                    return;
                }
                if ($lockedPayment->status === 'paid' || $lockedPayment->settlement_status === 'verified_settled') {
                    throw ValidationException::withMessages([
                        'receipt' => 'This group booking has already been verified and settled.',
                    ]);
                }

                $storedPath = $request->file('receipt')->store('bank-transfer-receipts', 'local');
                $lockedPayment->forceFill([
                    'bank_transfer_date' => $data['bank_transfer_date'],
                    'bank_transfer_receipt_path' => $storedPath,
                    'submitted_at' => now(),
                    'settlement_status' => 'pending_verification',
                    'status' => 'pending',
                ])->save();
            });
        } catch (\Throwable $exception) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }
            throw $exception;
        }

        return redirect()->route('agent.closed-groups.show', $booking->id)
            ->with('status', 'Bank-transfer proof submitted. Admin will review and verify the payment.');
    }

    public function downloadBankTransferProof(Request $request, int $booking)
    {
        $agent = $this->agentFor($request);
        $booking = $agent->bookings()
            ->where('booking_type', 'open-group')
            ->findOrFail($booking);
        $payment = $booking->payments()
            ->where('booking_ref_id', $booking->booking_ref_id)
            ->firstOrFail();
        $path = $payment->bank_transfer_receipt_path;
        abort_if(!$path || !Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download(
            $path,
            'bank-transfer-receipt-' . $booking->id . '.' . pathinfo($path, PATHINFO_EXTENSION)
        );
    }

    public function create(Request $request)
    {
        $agent = $this->agentFor($request);
        $this->ensureApproved($agent);
        $groups = $this->eligibleGroupsFor($agent);

        $closedGroupRoutes = [
            'rooms' => route('agent.closed-groups.rooms', ['group' => '__GROUP__']),
            'price' => route('agent.closed-groups.price', ['group' => '__GROUP__']),
            'csrf' => route('agent.closed-groups.csrf-token'),
            'store' => route('agent.closed-groups.book.store'),
            'success' => route('agent.closed-groups.index'),
        ];

        return view('admin.closed_groups.book', [
            'groups' => $groups,
            'isAgent' => true,
            'bookingCreatorLabel' => $agent->agency_name . ' · ' . $this->actorName($request),
            'closedGroupRoutes' => $closedGroupRoutes,
        ]);
    }

    public function rooms(Request $request, int $group)
    {
        $agent = $this->agentFor($request);
        $this->ensureApproved($agent);
        $this->findEligibleGroup($agent, $group);

        return app(AdminClosedGroupBookingController::class)->rooms(Group::findOrFail($group));
    }

    public function price(Request $request, int $group)
    {
        $agent = $this->agentFor($request);
        $this->ensureApproved($agent);
        $eligibleGroup = $this->findEligibleGroup($agent, $group);

        return app(AdminClosedGroupBookingController::class)->price($eligibleGroup, $request);
    }

    public function csrfToken(Request $request)
    {
        $this->agentFor($request);

        return response()->json(['token' => csrf_token()]);
    }

    public function store(Request $request)
    {
        $agent = $this->agentFor($request);
        $this->ensureApproved($agent);
        $this->findEligibleGroup($agent, (int) $request->input('group_id'));

        return app(AdminClosedGroupBookingController::class)->store($request);
    }

    private function agentFor(Request $request): AgentAccount
    {
        $user = Auth::guard('agent')->user() ?? Auth::guard('agent_staff')->user();
        $agent = $user instanceof AgentStaffUser ? $user->agent : $user;
        abort_unless($agent instanceof AgentAccount, 403);

        return $agent;
    }

    private function ensureApproved(AgentAccount $agent): void
    {
        abort_unless($agent->account_status === 'approved', 403, 'Agent account approval is required to create bookings.');
    }

    private function eligibleGroupsFor(AgentAccount $agent)
    {
        $today = today();

        return Group::query()
            ->where('country_id', $agent->country_id)
            ->whereRaw("LOWER(TRIM(group_type)) IN (?, ?)", ['closed group', 'close group'])
            ->whereRaw('LOWER(status) = ?', ['published'])
            ->whereNotNull('available_from')
            ->whereNotNull('available_to')
            ->whereDate('available_to', '>=', $today)
            ->orderByDesc('available_from')
            ->get()
            ->filter(fn (Group $group) => ClosedGroupBookingDates::hasBookableStartDate($group, $today)
                && max(2, (int) ($group->minimum_pax ?? 2))
                    <= min(20, (int) ($group->maximum_pax ?: 20))
                && ((int) ($group->maximum_pax ?? 0) === 0 || (int) $group->maximum_pax >= 2)
                && $this->itineraryBelongsToCountry($group, (int) $agent->country_id))
            ->map(function (Group $group) use ($today): Group {
                $group->setAttribute(
                    'agent_booking_min_start_date',
                    ClosedGroupBookingDates::earliestBookableStartDate($group, $today)?->toDateString()
                );
                $group->setAttribute(
                    'agent_booking_max_start_date',
                    ClosedGroupBookingDates::latestBookableStartDate($group)?->toDateString()
                );

                return $group;
            })
            ->values();
    }

    private function findEligibleGroup(AgentAccount $agent, int $groupId): Group
    {
        $group = $this->eligibleGroupsFor($agent)->firstWhere('id', $groupId);
        abort_unless($group instanceof Group, 404, 'This group is not available to your agency.');

        return $group;
    }

    private function itineraryBelongsToCountry(Group $group, int $countryId): bool
    {
        try {
            CountryOwnership::assertItineraryCountry((array) ($group->itinerary ?? []), $countryId);
        } catch (ValidationException) {
            return false;
        }

        return true;
    }

    private function actorName(Request $request): string
    {
        $user = Auth::guard('agent')->user() ?? Auth::guard('agent_staff')->user();

        return $user instanceof AgentStaffUser ? $user->full_name : ($user->contact_person ?? $user->agency_name);
    }
}
