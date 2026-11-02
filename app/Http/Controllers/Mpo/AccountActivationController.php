<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountActivationController extends Controller
{
    public function show(AdminUser $administrator, string $token)
    {
        $this->ensureValidToken($administrator, $token);

        return view('mpo.auth.activate', compact('administrator', 'token'));
    }

    public function update(Request $request, AdminUser $administrator, string $token)
    {
        $this->ensureValidToken($administrator, $token);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $administrator->password_hash = Hash::make($data['password']);
        $administrator->mpo_password_setup_token_hash = null;
        $administrator->mpo_password_setup_expires_at = null;
        $administrator->save();

        return redirect()->route('mpo.login')->with('success', 'Your password has been set. You can now sign in.');
    }

    private function ensureValidToken(AdminUser $administrator, string $token): void
    {
        abort_unless(
            $administrator->admin_type === 'mpo'
                && $administrator->mpo_password_setup_token_hash
                && hash_equals($administrator->mpo_password_setup_token_hash, hash('sha256', $token))
                && $administrator->mpo_password_setup_expires_at?->isFuture(),
            404,
        );
    }
}
