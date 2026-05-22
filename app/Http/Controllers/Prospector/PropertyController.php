<?php

namespace App\Http\Controllers\Prospector;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    /**
     * Liste des propriétés prospectées
     */
    public function index()
    {
        $properties = Auth::user()
            ->prospectedProperties()
            ->with('owner')
            ->withCount('commissions')
            ->latest()
            ->paginate(12);

        $stats = [
            'total' => Auth::user()->prospectedProperties()->count(),
            'validated' => Auth::user()->prospectedProperties()->where('prospector_validated', true)->count(),
            'approved' => Auth::user()->prospectedProperties()->where('status', 'approuve')->count(),
            'pending' => Auth::user()->prospectedProperties()->where('prospector_validated', false)->count(),
        ];

        return view('prospector.properties.index', compact('properties', 'stats'));
    }

    /**
     * Formulaire pour lier une propriété à un propriétaire
     */
    public function linkForm(Property $property)
    {
        // Vérifier que le démarcheur est bien le prospecteur
        if ($property->prospector_id !== Auth::id()) {
            abort(403);
        }

        return view('prospector.properties.link', compact('property'));
    }

    /**
     * Lier une propriété à un propriétaire
     */
    public function link(Request $request, Property $property)
    {
        if ($property->prospector_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'owner_email' => ['required', 'email', 'exists:users,email'],
        ]);

        $owner = User::where('email', $validated['owner_email'])->first();

        if (!$owner->isProprietaire()) {
            return back()->with('error', 'Cet utilisateur n\'est pas enregistré comme propriétaire.');
        }

        $property->update([
            'owner_id' => $owner->id,
            'prospector_validated' => false,
        ]);

        // Notifier le propriétaire
        Notification::send(
            $owner,
            'prospector_validation',
            'Nouveau bien à valider',
            "Un démarcheur a ajouté un bien en votre nom : {$property->title}",
            route('dashboard.owner.properties.validate')
        );

        return redirect()->route('dashboard.demarcheur.properties')
            ->with('success', 'Demande de liaison envoyée au propriétaire.');
    }

    /**
     * Rechercher des propriétaires
     */
    public function searchOwners(Request $request)
    {
        $query = $request->get('q');

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $owners = User::where('user_type', 'proprietaire')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->take(10)
            ->get(['id', 'name', 'email']);

        return response()->json($owners);
    }
}


