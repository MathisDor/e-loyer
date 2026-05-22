<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Afficher le profil de l'utilisateur connecté
     */
    public function show(): View
    {
        $user = Auth::user();
        $user->load(['reviewsReceived' => function ($query) {
            $query->where('is_approved', true)->latest()->take(5);
        }]);

        return view('profile.show', compact('user'));
    }

    /**
     * Afficher le profil public d'un utilisateur
     */
    public function public(User $user): View
    {
        $user->load(['reviewsReceived' => function ($query) {
            $query->where('is_approved', true)->latest()->take(10);
        }]);

        // Si propriétaire, charger ses propriétés approuvées
        $properties = collect();
        if ($user->isProprietaire()) {
            $properties = $user->properties()
                ->where('status', 'approuve')
                ->where('is_available', true)
                ->take(6)
                ->get();
        }

        return view('profile.public', compact('user', 'properties'));
    }

    /**
     * Formulaire d'édition du profil
     */
    public function edit(): View
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Mettre à jour le profil
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        // Upload de l'avatar
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Check if email changed
        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Formulaire de changement de mot de passe
     */
    public function password(): View
    {
        return view('profile.password');
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Mot de passe modifié avec succès.');
    }

    /**
     * Upload de la pièce d'identité
     */
    public function uploadIdCard(Request $request): RedirectResponse
    {
        $request->validate([
            'id_card' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = Auth::user();

        if ($user->id_card) {
            Storage::disk('public')->delete($user->id_card);
        }

        $path = $request->file('id_card')->store('id_cards', 'public');
        $user->update(['id_card' => $path]);

        return back()->with('success', 'Pièce d\'identité téléversée. Elle sera vérifiée par notre équipe.');
    }

    /**
     * Upload du bulletin de salaire (locataires)
     */
    public function uploadPaySlip(Request $request): RedirectResponse
    {
        $request->validate([
            'pay_slip' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = Auth::user();

        if ($user->pay_slip) {
            Storage::disk('public')->delete($user->pay_slip);
        }

        $path = $request->file('pay_slip')->store('documents/pay_slips', 'public');
        $user->update(['pay_slip' => $path]);

        return back()->with('success', 'Bulletin de salaire téléversé avec succès.');
    }

    /**
     * Upload du contrat de travail (locataires)
     */
    public function uploadEmploymentContract(Request $request): RedirectResponse
    {
        $request->validate([
            'employment_contract' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = Auth::user();

        if ($user->employment_contract) {
            Storage::disk('public')->delete($user->employment_contract);
        }

        $path = $request->file('employment_contract')->store('documents/employment_contracts', 'public');
        $user->update(['employment_contract' => $path]);

        return back()->with('success', 'Contrat de travail téléversé avec succès.');
    }

    /**
     * Upload du justificatif de domicile (locataires)
     */
    public function uploadProofOfAddress(Request $request): RedirectResponse
    {
        $request->validate([
            'proof_of_address' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = Auth::user();

        if ($user->proof_of_address) {
            Storage::disk('public')->delete($user->proof_of_address);
        }

        $path = $request->file('proof_of_address')->store('documents/proof_of_address', 'public');
        $user->update(['proof_of_address' => $path]);

        return back()->with('success', 'Justificatif de domicile téléversé avec succès.');
    }

    /**
     * Upload du relevé bancaire (locataires)
     */
    public function uploadBankStatement(Request $request): RedirectResponse
    {
        $request->validate([
            'bank_statement' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = Auth::user();

        if ($user->bank_statement) {
            Storage::disk('public')->delete($user->bank_statement);
        }

        $path = $request->file('bank_statement')->store('documents/bank_statements', 'public');
        $user->update(['bank_statement' => $path]);

        return back()->with('success', 'Relevé bancaire téléversé avec succès.');
    }

    /**
     * Upload du titre de propriété (propriétaires/agences)
     */
    public function uploadPropertyTitle(Request $request): RedirectResponse
    {
        $request->validate([
            'property_title' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = Auth::user();

        if ($user->property_title) {
            Storage::disk('public')->delete($user->property_title);
        }

        $path = $request->file('property_title')->store('documents/property_titles', 'public');
        $user->update(['property_title' => $path]);

        return back()->with('success', 'Titre de propriété téléversé avec succès.');
    }

    /**
     * Upload du registre de commerce (agences)
     */
    public function uploadBusinessRegistration(Request $request): RedirectResponse
    {
        $request->validate([
            'business_registration' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = Auth::user();

        if ($user->business_registration) {
            Storage::disk('public')->delete($user->business_registration);
        }

        $path = $request->file('business_registration')->store('documents/business_registrations', 'public');
        $user->update(['business_registration' => $path]);

        return back()->with('success', 'Registre de commerce téléversé avec succès.');
    }

    /**
     * Favoris de l'utilisateur
     */
    public function favorites(): View
    {
        $favorites = Auth::user()
            ->favoriteProperties()
            ->with('owner')
            ->paginate(12);

        return view('profile.favorites', compact('favorites'));
    }

    /**
     * Supprimer le compte
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
