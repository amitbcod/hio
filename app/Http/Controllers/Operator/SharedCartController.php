<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CartItemBuilderTrait;
use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\SharedCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SharedCartController extends Controller
{
    use CartItemBuilderTrait;

    public function index()
    {
        $operatorId = Auth::guard('operator')->id();
        $sharedCarts = SharedCart::where('owner_type', 'operator')
            ->where('owner_id', $operatorId)
            ->orderByDesc('created_at')
            ->get();

        return view('shared_carts.index', [
            'layout' => 'layouts.app',
            'routePrefix' => 'operator',
            'sharedCarts' => $sharedCarts,
        ]);
    }

    public function create()
    {
        return view('shared_carts.create', [
            'layout' => 'layouts.app',
            'routePrefix' => 'operator',
        ]);
    }

    public function store(Request $request)
    {
        $operatorId = Auth::guard('operator')->id();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $sharedCart = SharedCart::create([
            'owner_type' => 'operator',
            'owner_id' => $operatorId,
            'title' => $validated['title'],
            'token' => Str::random(40),
            'items' => [],
            'status' => 'Active',
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()->route('frontend.booking.shared.init', $sharedCart->token)
            ->with('success', 'Shareable cart created. You are being redirected to the frontend to add items.');
    }

    public function show(SharedCart $sharedCart)
    {
        $this->ensureOwner($sharedCart);

        $accommodations = Accommodation::where('operator_id', Auth::guard('operator')->id())->get();
        $activities = Activity::where('operator_id', Auth::guard('operator')->id())->get();

        return view('shared_carts.show', [
            'layout' => 'layouts.app',
            'routePrefix' => 'operator',
            'sharedCart' => $sharedCart,
            'accommodations' => $accommodations,
            'activities' => $activities,
        ]);
    }

    public function storeItem(Request $request, SharedCart $sharedCart)
    {
        $this->ensureOwner($sharedCart);

        $type = $request->input('type');

        $validator = Validator::make($request->all(), [
            'type' => ['required', Rule::in(['accommodation', 'activity'])],
            'check_in' => 'required|date',
            'total_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'adults' => 'nullable|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'infants' => 'nullable|integer|min:0',
        ]);

        if ($type === 'accommodation') {
            $validator->sometimes('accommodation_id', ['required', 'integer', Rule::exists('accommodations', 'id')->where(function ($query) {
                $query->where('operator_id', Auth::guard('operator')->id());
            })], function () {
                return true;
            });
            $validator->sometimes('check_out', 'required|date|after_or_equal:check_in', function () {
                return true;
            });
        }

        if ($type === 'activity') {
            $validator->sometimes('activity_id', ['required', 'integer', Rule::exists('activities', 'id')->where(function ($query) {
                $query->where('operator_id', Auth::guard('operator')->id());
            })], function () {
                return true;
            });
        }

        $validator->validate();

        $item = $type === 'accommodation'
            ? $this->buildAccommodationCartItem($request)
            : $this->buildActivityCartItem($request);

        $sharedCart->items = array_values(array_merge($sharedCart->items ?? [], [$item]));
        $sharedCart->save();

        return redirect()->route('operator.shared-carts.show', $sharedCart)
            ->with('success', 'Item added to shareable cart.');
    }

    public function removeItem(SharedCart $sharedCart, string $itemKey)
    {
        $this->ensureOwner($sharedCart);

        $items = collect($sharedCart->items ?? [])->filter(function ($item) use ($itemKey) {
            return ($item['cart_key'] ?? '') !== $itemKey;
        })->values()->all();

        $sharedCart->items = $items;
        $sharedCart->save();

        return redirect()->route('operator.shared-carts.show', $sharedCart)
            ->with('success', 'Item removed from shareable cart.');
    }

    private function ensureOwner(SharedCart $sharedCart): void
    {
        if ($sharedCart->owner_type !== 'operator' || $sharedCart->owner_id !== Auth::guard('operator')->id()) {
            abort(403);
        }
    }
}
