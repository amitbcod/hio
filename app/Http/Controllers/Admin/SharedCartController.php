<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CartItemBuilderTrait;
use App\Models\Accommodation;
use App\Models\Activity;
use App\Models\SharedCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SharedCartController extends Controller
{
    use CartItemBuilderTrait;

    private function adminId(): ?int
    {
        return (int) session('admin_id') ?: null;
    }

    private function checkAdmin(): void
    {
        if (!$this->adminId()) {
            redirect()->route('admin.login')->send();
        }
    }

    public function index()
    {
        $this->checkAdmin();

        $sharedCarts = SharedCart::where('owner_type', 'admin')
            ->where('owner_id', $this->adminId())
            ->orderByDesc('created_at')
            ->get();

        return view('shared_carts.index', [
            'layout' => 'layouts.admin',
            'routePrefix' => 'admin',
            'sharedCarts' => $sharedCarts,
        ]);
    }

    public function create()
    {
        $this->checkAdmin();

        return view('shared_carts.create', [
            'layout' => 'layouts.admin',
            'routePrefix' => 'admin',
        ]);
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $sharedCart = SharedCart::create([
            'owner_type' => 'admin',
            'owner_id' => $this->adminId(),
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
        $this->checkAdmin();
        $this->ensureOwner($sharedCart);

        $accommodations = Accommodation::all();
        $activities = Activity::all();

        return view('shared_carts.show', [
            'layout' => 'layouts.admin',
            'routePrefix' => 'admin',
            'sharedCart' => $sharedCart,
            'accommodations' => $accommodations,
            'activities' => $activities,
        ]);
    }

    public function storeItem(Request $request, SharedCart $sharedCart)
    {
        $this->checkAdmin();
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
            $validator->sometimes('accommodation_id', ['required', 'integer', Rule::exists('accommodations', 'id')], function () {
                return true;
            });
            $validator->sometimes('check_out', 'required|date|after_or_equal:check_in', function () {
                return true;
            });
        }

        if ($type === 'activity') {
            $validator->sometimes('activity_id', ['required', 'integer', Rule::exists('activities', 'id')], function () {
                return true;
            });
        }

        $validator->validate();

        $item = $type === 'accommodation'
            ? $this->buildAccommodationCartItem($request)
            : $this->buildActivityCartItem($request);

        $sharedCart->items = array_values(array_merge($sharedCart->items ?? [], [$item]));
        $sharedCart->save();

        return redirect()->route('admin.shared-carts.show', $sharedCart)
            ->with('success', 'Item added to shareable cart.');
    }

    public function removeItem(SharedCart $sharedCart, string $itemKey)
    {
        $this->checkAdmin();
        $this->ensureOwner($sharedCart);

        $items = collect($sharedCart->items ?? [])->filter(function ($item) use ($itemKey) {
            return ($item['cart_key'] ?? '') !== $itemKey;
        })->values()->all();

        $sharedCart->items = $items;
        $sharedCart->save();

        return redirect()->route('admin.shared-carts.show', $sharedCart)
            ->with('success', 'Item removed from shareable cart.');
    }

    private function ensureOwner(SharedCart $sharedCart): void
    {
        if ($sharedCart->owner_type !== 'admin' || $sharedCart->owner_id !== $this->adminId()) {
            abort(403);
        }
    }
}
