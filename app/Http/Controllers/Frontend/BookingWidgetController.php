<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BookingWidget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookingWidgetController extends Controller
{
    public function generate(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        // ensure operator can only generate for themselves
        $widget = BookingWidget::firstOrNew(['operator_id' => $user->id]);
        $widget->widget_token = Str::random(48);
        $widget->is_active = true;
        $widget->save();

        return redirect()->route('operator.booking-widget.script')->with('widget_token', $widget->widget_token);
    }

    public function showScript(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $widget = BookingWidget::where('operator_id', $user->id)->first();
        return view('operator.booking-widget-script', ['widget' => $widget]);
    }

    public function validateToken(string $token)
    {
        $widget = BookingWidget::where('widget_token', $token)->where('is_active', true)->first();
        if (!$widget) {
            return response()->json(['valid' => false], 404);
        }

        return response()->json([
            'valid' => true,
            'operator_id' => $widget->operator_id,
        ]);
    }

    public function trackRedirect(Request $request)
    {
        $token = (string) $request->query('token', '');
        $service = (string) $request->query('service', '');

        $widget = BookingWidget::where('widget_token', $token)->where('is_active', true)->first();
        if (!$widget) {
            abort(403, 'Invalid widget token');
        }

        // sanitize inputs - allow only expected param keys
        $allowed = [
            'destination', 'check_in', 'check_out', 'guests', 'rooms',
            'activity_date', 'travellers',
            'pickup', 'dropoff', 'pickup_date', 'pickup_time', 'passengers',
            'arrival_date', 'arrival_time', 'return_date', 'return_time',
            'operator_token',
        ];

        $query = [];
        foreach ($request->query() as $k => $v) {
            if (in_array($k, $allowed, true)) {
                $query[$k] = is_array($v) ? implode(',', $v) : strip_tags((string) $v);
            }
        }

        // include operator token for tracking
        $query['operator_token'] = $token;

        // Normalize widget params to frontend expected keys
        if ($service === 'accommodation') {
            if (isset($query['guests'])) {
                $query['adults'] = (int) $query['guests'];
                unset($query['guests']);
            }
        }

        if ($service === 'activity') {
            unset($query['guests']);
            if (isset($query['travellers'])) {
                $query['adults'] = (int) $query['travellers'];
                $query['participants'] = (int) $query['travellers'];
            }
            unset($query['travellers']);
        }

        if ($service === 'transport') {
            if (isset($query['pickup_date'])) {
                $query['arrival_date'] = $query['pickup_date'];
            }
            if (isset($query['pickup_time'])) {
                $query['arrival_time'] = $query['pickup_time'];
            }
        }

        // Decide base path per service
        $base = '/category-list?category=' . ($service === 'transport' ? 'transport' : ($service === 'activity' ? 'tours' : 'accommodation'));

        $qs = http_build_query($query);
        $url = url($base) . ($qs ? '&' . $qs : '');

        // Log tracking event
        Log::info('Widget redirect tracked', ['operator_id' => $widget->operator_id, 'service' => $service, 'query' => $query]);

        return redirect()->to($url);
    }
}
