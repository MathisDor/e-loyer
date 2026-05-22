<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('type')) {
            $query->where('user_type', $request->type);
        }

        if ($request->filled('verified')) {
            $query->where('is_verified', $request->boolean('verified'));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Afficher un utilisateur
     */
    public function show(User $user)
    {
        $user->load([
            'properties' => fn($q) => $q->latest()->take(5),
            'prospectedProperties' => fn($q) => $q->latest()->take(5),
            'bookingsAsTenant' => fn($q) => $q->latest()->take(5),
            'bookingsAsOwner' => fn($q) => $q->latest()->take(5),
            'commissions' => fn($q) => $q->latest()->take(5),
        ]);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Vérifier un utilisateur
     */
    public function verify(User $user)
    {
        $user->update(['is_verified' => true]);

        return back()->with('success', 'Utilisateur vérifié avec succès.');
    }

    /**
     * Révoquer la vérification
     */
    public function unverify(User $user)
    {
        $user->update(['is_verified' => false]);

        return back()->with('success', 'Vérification révoquée.');
    }

    /**
     * Modifier le type d'utilisateur
     */
    public function updateType(Request $request, User $user)
    {
        $request->validate([
            'user_type' => ['required', 'in:locataire,proprietaire,demarcheur,admin'],
        ]);

        $user->update(['user_type' => $request->user_type]);

        return back()->with('success', 'Type d\'utilisateur modifié.');
    }

    /**
     * Suspendre un compte
     */
    public function suspend(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas suspendre votre propre compte.');
        }
        if ($user->isAdmin()) {
            return back()->with('error', 'Impossible de suspendre un administrateur.');
        }

        $request->validate([
            'suspension_reason' => ['required', 'string', 'max:500'],
        ]);

        $user->update([
            'is_suspended'      => true,
            'suspension_reason' => $request->suspension_reason,
        ]);

        return back()->with('success', "Compte de {$user->name} suspendu.");
    }

    /**
     * Réactiver un compte suspendu
     */
    public function unsuspend(User $user)
    {
        $user->update([
            'is_suspended'      => false,
            'suspension_reason' => null,
        ]);

        return back()->with('success', "Compte de {$user->name} réactivé.");
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé.');
    }
}


