<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\TravelerPasswordResetMail;
use App\Mail\TravelerWelcomeMail;
use App\Models\TravelerAccount;
use App\Models\TravelerCart;
use App\Models\TravelerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class TravelerAuthController extends Controller
{
    public function showRegisterForm()
    {
        if (Auth::guard('traveler')->check()) {
            return redirect()->route('traveler.profile');
        }

        return view('frontend.traveler.register', [
            'countries' => $this->countries(),
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'country' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:traveler_accounts,email'],
            'mobile_phone' => ['required', 'string', 'max:25', 'regex:/^\+[1-9]\d{6,14}$/', 'unique:traveler_accounts,mobile_phone'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&^()_+\-=\[\]{};:\"\\|,.<>\/?]/',
            ],
            'consent_terms' => ['accepted'],
            'consent_privacy' => ['accepted'],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ], [
            'mobile_phone.regex' => 'Mobile phone must be in E.164 format (example: +23052511153).',
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
        ]);

        $nameParts = $this->splitName($request->input('full_name'));

        $account = TravelerAccount::create([
            'traveler_id' => $this->generateTravelerId(),
            'full_name' => trim($request->input('full_name')),
            'country' => $request->input('country'),
            'email' => strtolower(trim($request->input('email'))),
            'mobile_phone' => trim($request->input('mobile_phone')),
            'password_hash' => Hash::make($request->input('password')),
            'verification_status' => 'Unverified',
            'terms_accepted_at' => now(),
            'terms_version' => 'TNC-2026.03',
            'privacy_accepted_at' => now(),
            'privacy_version' => 'PRIVACY-2026.03',
            'marketing_opt_in' => $request->boolean('marketing_opt_in'),
        ]);

        TravelerProfile::create([
            'traveler_account_id' => $account->id,
            'first_name' => $nameParts['first_name'],
            'middle_name' => $nameParts['middle_name'],
            'last_name' => $nameParts['last_name'],
            'country' => $request->input('country'),
            'preferred_language' => 'EN',
        ]);

        // Send welcome email
        try {
            Mail::to($account->email)->send(new TravelerWelcomeMail($account));
        } catch (\Exception $e) {
            \Log::error('Traveler welcome email failed', [
                'email' => $account->email,
                'error' => $e->getMessage(),
            ]);
        }

        Auth::guard('traveler')->login($account);
        $request->session()->regenerate();
        $this->syncCartAfterAuthentication($account->id, $request);

        return redirect()->route('traveler.profile')
            ->with('success', 'Traveler account created successfully. Please complete your profile.');
    }

    public function showLoginForm()
    {
        if (Auth::guard('traveler')->check()) {
            return redirect()->route('traveler.profile');
        }

        return view('frontend.traveler.login');
    }

    public function showForgotPasswordForm()
    {
        if (Auth::guard('traveler')->check()) {
            return redirect()->route('traveler.profile');
        }

        return view('frontend.traveler.passwords.email');
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($request->input('email')));
        $account = TravelerAccount::where('email', $email)->first();

        if ($account) {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $account->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            try {
                Mail::to($account->email)->send(new TravelerPasswordResetMail($account, $token));
            } catch (\Exception $e) {
                \Log::error('Traveler password reset email failed', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('status', 'If an account exists for that email, a password reset link has been sent.');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('frontend.traveler.passwords.reset', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&^()_+\-=\[\]{};:"\\|,.<>\/\?]/',
            ],
        ], [
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
        ]);

        $email = strtolower(trim($request->input('email')));
        $token = $request->input('token');
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();
        $expireMinutes = config('auth.passwords.travelers.expire', 60);

        if (! $record || ! Hash::check($token, $record->token) || Carbon::parse($record->created_at)->addMinutes($expireMinutes)->isPast()) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.'])->withInput();
        }

        $account = TravelerAccount::where('email', $email)->first();
        if (! $account) {
            return back()->withErrors(['email' => 'Unable to find traveler account.'])->withInput();
        }

        $account->password_hash = Hash::make($request->input('password'));
        $account->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route('traveler.login')->with('success', 'Your password has been reset. Please sign in with your new password.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = [
            'email' => strtolower(trim($request->input('email'))),
            'password' => $request->input('password'),
        ];

        if (!Auth::guard('traveler')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Invalid traveler credentials.'])
                ->withInput($request->only('email'));
        }

        $traveler = Auth::guard('traveler')->user();

        // Check if account is suspended
        if ($traveler->account_suspended) {
            Auth::guard('traveler')->logout();
            $request->session()->invalidate();

            return back()
                ->withErrors(['email' => 'Your account has been suspended. Please contact support to reactivate it.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        $traveler->last_login_at = now();
        $traveler->save();

        $this->syncCartAfterAuthentication($traveler->id, $request);

        $redirect = $request->input('redirect') ?: $request->query('redirect');
        if ($redirect) {
            $parsed = parse_url($redirect);
            if (!isset($parsed['host']) || $parsed['host'] === $request->getHost()) {
                return redirect()->intended($redirect);
            }
        }

        return redirect()->intended(route('traveler.profile'));
    }

    public function logout(Request $request)
    {
        $traveler = Auth::guard('traveler')->user();

        if ($traveler) {
            $this->persistSessionCartForTraveler((int) $traveler->id, $request);
        }

        Auth::guard('traveler')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('frontend.home');
    }

    private function generateTravelerId(): string
    {
        do {
            $candidate = 'TRV' . now()->format('Ymd') . strtoupper(Str::random(6));
        } while (TravelerAccount::where('traveler_id', $candidate)->exists());

        return $candidate;
    }

    private function splitName(string $fullName): array
    {
        $tokens = collect(preg_split('/\s+/', trim($fullName)) ?: [])->filter()->values();

        if ($tokens->isEmpty()) {
            return [
                'first_name' => null,
                'middle_name' => null,
                'last_name' => null,
            ];
        }

        $firstName = (string) $tokens->first();
        $lastName = $tokens->count() > 1 ? (string) $tokens->last() : null;

        $middleName = null;
        if ($tokens->count() > 2) {
            $middleName = $tokens->slice(1, $tokens->count() - 2)->implode(' ');
        }

        return [
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
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

    private function syncCartAfterAuthentication(int $travelerId, Request $request): void
    {
        $sessionCart = $request->session()->get('booking_cart', []);

        $storedCartRecord = TravelerCart::where('traveler_account_id', $travelerId)->first();
        $storedCart = is_array($storedCartRecord?->items) ? $storedCartRecord->items : [];

        $merged = $storedCart;
        foreach ($sessionCart as $cartKey => $item) {
            $merged[$cartKey] = $item;
        }

        if (empty($merged)) {
            $request->session()->forget('booking_cart');
        } else {
            $request->session()->put('booking_cart', $merged);
        }

        TravelerCart::updateOrCreate(
            ['traveler_account_id' => $travelerId],
            ['items' => empty($merged) ? null : $merged]
        );
    }

    private function persistSessionCartForTraveler(int $travelerId, Request $request): void
    {
        $sessionCart = $request->session()->get('booking_cart', []);

        TravelerCart::updateOrCreate(
            ['traveler_account_id' => $travelerId],
            ['items' => empty($sessionCart) ? null : $sessionCart]
        );
    }
}
