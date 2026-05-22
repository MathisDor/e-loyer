<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Notification;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Liste des propriétés en attente
     */
    public function pending()
    {
        $properties = Property::with(['owner', 'prospector'])
            ->pending()
            ->latest()
            ->paginate(20);

        return view('admin.properties.pending', compact('properties'));
    }

    /**
     * Toutes les propriétés
     */
    public function index(Request $request)
    {
        $query = Property::with(['owner', 'prospector']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('address', 'like', "%{$request->search}%");
            });
        }

        $properties = $query->latest()->paginate(20);

        return view('admin.properties.index', compact('properties'));
    }

    /**
     * Afficher une propriété
     */
    public function show(Property $property)
    {
        $property->load(['owner', 'prospector', 'bookings', 'reviews']);

        return view('admin.properties.show', compact('property'));
    }

    /**
     * Approuver une propriété
     */
    public function approve(Property $property)
    {
        $property->update(['status' => 'approuve']);

        // Notifier le propriétaire
        Notification::send(
            $property->owner,
            'property_approved',
            'Propriété approuvée',
            "Votre propriété \"{$property->title}\" a été approuvée et est maintenant visible.",
            route('properties.show', $property)
        );

        // Notifier le démarcheur si applicable
        if ($property->prospector) {
            Notification::send(
                $property->prospector,
                'property_approved',
                'Propriété approuvée',
                "La propriété \"{$property->title}\" que vous avez prospectée a été approuvée.",
                route('properties.show', $property)
            );
        }

        return back()->with('success', 'Propriété approuvée avec succès.');
    }

    /**
     * Rejeter une propriété
     */
    public function reject(Request $request, Property $property)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $property->update([
            'status' => 'rejete',
            'rejection_reason' => $request->reason,
        ]);

        // Notifier le propriétaire
        Notification::send(
            $property->owner,
            'property_rejected',
            'Propriété rejetée',
            "Votre propriété \"{$property->title}\" a été rejetée. Raison : {$request->reason}",
            route('dashboard')
        );

        return back()->with('success', 'Propriété rejetée.');
    }

    /**
     * Supprimer une propriété
     */
    public function destroy(Property $property)
    {
        $property->delete();

        return redirect()->route('admin.properties.index')
            ->with('success', 'Propriété supprimée.');
    }
}


