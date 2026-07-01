<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    public function show(Request $request)
    {
        $panel = match ($request->user()?->role) {
            'platform_admin'      => 'platform',
            'parent'              => 'parent',
            'teacher', 'employee' => 'staff',
            default               => 'admin',
        };

        return view('auth.change-password', [
            'logoutUrl' => route("filament.{$panel}.auth.logout"),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [], [
            'current_password' => __('mot de passe actuel'),
            'password'         => __('nouveau mot de passe'),
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => __('Le mot de passe actuel est incorrect.')]);
        }

        $user->forceFill([
            'password'             => Hash::make($validated['password']),
            'must_change_password' => false,
        ])->save();

        return redirect($this->homeFor($user))->with('status', __('Mot de passe mis à jour avec succès.'));
    }

    protected function homeFor($user): string
    {
        return match ($user->role) {
            'platform_admin'     => '/platform',
            'admin'              => '/admin',
            'parent'             => '/parent',
            'teacher', 'employee' => '/staff',
            default              => '/',
        };
    }
}
