<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'user_type' => ['required', 'in:locataire,proprietaire,demarcheur,agence'],
            'phone' => ['required', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'agency_name' => ['required_if:user_type,agence', 'nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp ?? $request->phone,
            'city' => $request->city,
            'agency_name' => $request->user_type === 'agence' ? $request->agency_name : null,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Bienvenue sur E-Loyer ! Votre compte a été créé avec succès.');
    }
}


