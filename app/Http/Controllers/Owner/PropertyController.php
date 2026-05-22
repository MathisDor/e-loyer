<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    /**
     * Liste des propriétés du propriétaire
     */
    public function index()
    {
        $properties = Auth::user()
            ->properties()
            ->withCount(['bookings', 'favorites'])
            ->latest()
            ->paginate(12);

        return view('owner.properties.index', compact('properties'));
    }

    /**
     * Propriétés à valider (ajoutées par démarcheurs)
     */
    public function toValidate()
    {
        $properties = Property::with('prospector')
            ->where('owner_id', Auth::id())
            ->needsProspectorValidation()
            ->latest()
            ->get();

        return view('owner.properties.validate', compact('properties'));
    }

    /**
     * Valider une propriété ajoutée par un démarcheur
     */
    public function validateProperty(Property $property)
    {
        $this->authorize('validate', $property);

        $property->update(['prospector_validated' => true]);

        // Notifier le démarcheur
        if ($property->prospector) {
            Notification::send(
                $property->prospector,
                'property_approved',
                'Propriété validée par le propriétaire',
                "Le propriétaire a validé la propriété \"{$property->title}\" que vous avez prospectée.",
                route('dashboard.demarcheur.properties')
            );
        }

        return back()->with('success', 'Propriété validée. Elle sera visible après approbation par l\'administration.');
    }

    /**
     * Rejeter une propriété ajoutée par un démarcheur
     */
    public function rejectProperty(Request $request, Property $property)
    {
        $this->authorize('validate', $property);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        // Supprimer le lien avec le propriétaire
        $property->update([
            'owner_id' => $property->prospector_id, // Remettre au démarcheur
            'prospector_id' => null,
            'prospector_validated' => false,
            'rejection_reason' => $request->reason,
        ]);

        return back()->with('success', 'Propriété rejetée.');
    }

    /**
     * Changer la disponibilité
     */
    public function toggleAvailability(Property $property)
    {
        $this->authorize('update', $property);

        $property->update(['is_available' => !$property->is_available]);

        $message = $property->is_available 
            ? 'Propriété marquée comme disponible.' 
            : 'Propriété marquée comme indisponible.';

        return back()->with('success', $message);
    }
}


