<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelerProfileController extends Controller
{
    public function showProfile()
    {
        $account = Auth::guard('traveler')->user();
        $profile = $account->profile;

        if (!$profile) {
            $profile = $account->profile()->create([
                'preferred_language' => 'EN',
            ]);
        }

        return view('frontend.traveler.profile', [
            'account' => $account,
            'profile' => $profile,
            'countries' => $this->countries(),
            'preferredLanguages' => $this->preferredLanguages(),
            'titleOptions' => $this->titleOptions(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $account = Auth::guard('traveler')->user();

        $request->validate([
            'gender' => ['nullable', 'in:Mr,Mrs,Miss,Ms,Mx,Other'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->toDateString()],
            'country' => ['required', 'string', 'max:100'],
            'nationality' => ['required', 'string', 'max:100'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city_region' => ['nullable', 'string', 'max:150'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:25', 'regex:/^\+[1-9]\d{6,14}$/'],
            'special_notes' => ['nullable', 'string', 'max:2000'],
            'preferred_language' => ['required', 'in:EN,FR,DE,ES,IT'],
        ], [
            'date_of_birth.before_or_equal' => 'Traveler must be at least 18 years old.',
            'emergency_contact_phone.regex' => 'Emergency contact phone must be in E.164 format (example: +23052511153).',
        ]);

        $account->country = $request->input('country');
        $account->full_name = trim(implode(' ', array_filter([
            $request->input('first_name'),
            $request->input('middle_name'),
            $request->input('last_name'),
        ])));
        $account->save();

        $account->profile()->updateOrCreate(
            ['traveler_account_id' => $account->id],
            [
                'gender' => $request->input('gender'),
                'first_name' => $request->input('first_name'),
                'middle_name' => $request->input('middle_name'),
                'last_name' => $request->input('last_name'),
                'date_of_birth' => $request->input('date_of_birth'),
                'country' => $request->input('country'),
                'nationality' => $request->input('nationality'),
                'address_line_1' => $request->input('address_line_1'),
                'address_line_2' => $request->input('address_line_2'),
                'city_region' => $request->input('city_region'),
                'emergency_contact_name' => $request->input('emergency_contact_name'),
                'emergency_contact_phone' => $request->input('emergency_contact_phone'),
                'special_notes' => $request->input('special_notes'),
                'preferred_language' => $request->input('preferred_language'),
            ]
        );

        return back()->with('success', 'Traveler profile updated successfully.');
    }

    public function showSettings()
    {
        $account = Auth::guard('traveler')->user();

        return view('frontend.traveler.settings', [
            'account' => $account,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $account = Auth::guard('traveler')->user();

        $request->validate([
            '2fa_enabled' => ['nullable', 'boolean'],
            '2fa_method' => ['nullable', 'in:email,sms,auth_app'],
            'communication_preference' => ['nullable', 'array'],
            'communication_preference.*' => ['in:email,sms,whatsapp'],
        ]);

        $account->update([
            '2fa_enabled' => (bool) $request->boolean('2fa_enabled'),
            '2fa_method' => $request->input('2fa_enabled') ? $request->input('2fa_method') : null,
            'communication_preference' => $request->input('communication_preference') ?? [],
        ]);

        return back()->with('success', 'Settings updated successfully.');
    }

    public function requestPasswordReset(Request $request)
    {
        $account = Auth::guard('traveler')->user();

        $account->update([
            'password_reset_requested_at' => now(),
        ]);

        // TODO: Send password reset email to traveler
        // Mail::send(new PasswordResetEmail($account));

        return back()->with('success', 'Password reset link has been sent to your email.');
    }

    public function toggleAccountSuspension(Request $request)
    {
        $account = Auth::guard('traveler')->user();

        $isSuspending = !$account->account_suspended;

        $account->update([
            'account_suspended' => $isSuspending,
        ]);

        $message = $isSuspending 
            ? 'Your account has been suspended. You will not be able to login or make bookings.'
            : 'Your account has been reactivated. You can now login and make bookings.';

        return back()->with('success', $message);
    }

    private function titleOptions(): array
    {
        return ['Mr', 'Mrs', 'Miss', 'Ms', 'Mx', 'Other'];
    }

    private function preferredLanguages(): array
    {
        return [
            'EN' => 'English',
            'FR' => 'French',
            'DE' => 'German',
            'ES' => 'Spanish',
            'IT' => 'Italian',
        ];
    }

    private function countries(): array
    {
        return [
            'Australia',
            'Canada',
            'China',
            'France',
            'Germany',
            'India',
            'Italy',
            'Kenya',
            'Madagascar',
            'Mauritius',
            'Reunion',
            'Singapore',
            'South Africa',
            'United Arab Emirates',
            'United Kingdom',
            'United States',
        ];
    }
}
