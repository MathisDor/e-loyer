<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Commission;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Formulaire de réservation
     */
    public function create(Property $property)
    {
        if (!$property->is_available || $property->status !== 'approuve') {
            return back()->with('error', 'Cette propriété n\'est pas disponible à la location.');
        }

        if (Auth::id() === $property->owner_id) {
            return back()->with('error', 'Vous ne pouvez pas réserver votre propre propriété.');
        }

        return view('bookings.create', compact('property'));
    }

    /**
     * Enregistrer une demande de réservation
     */
    public function store(Request $request, Property $property)
    {
        $this->authorize('create', [Booking::class, $property]);

        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:24'],
            'message' => ['nullable', 'string', 'max:1000'],
            'accept_terms' => ['required', 'accepted'],
        ], [
            'accept_terms.required' => 'Vous devez accepter les règles et conditions de location.',
            'accept_terms.accepted' => 'Vous devez accepter les règles et conditions de location.',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $durationMonths = (int) $validated['duration_months'];
        $endDate = $startDate->copy()->addMonths($durationMonths);
        
        $monthlyAmount = $property->monthly_price;
        $totalAmount = $monthlyAmount * $durationMonths;
        $depositAmount = $property->deposit ?? $monthlyAmount;

        // Calculer les commissions
        $hasProspector = $property->hasProspector();
        $commissions = Booking::calculateCommissions($monthlyAmount, $hasProspector);

        // Récupérer le code de parrainage depuis la session (si locataire arrivé via lien ref)
        $referralCode   = session('referral_code');
        $referralUserId = session('referral_user_id');

        // Anti auto-parrainage : ignorer si le démarcheur réserve son propre lien
        if ($referralUserId && $referralUserId === Auth::id()) {
            $referralCode   = null;
            $referralUserId = null;
        }

        // Si pas de prospecteur sur le bien mais un lien ref => on attache le démarcheur
        if ($referralUserId && !$property->prospector_id) {
            $property->update([
                'prospector_id'        => $referralUserId,
                'prospector_validated' => true,
            ]);
            $hasProspector = true;
            $commissions   = Booking::calculateCommissions($monthlyAmount, true);
        }

        $booking = Booking::create([
            'property_id'           => $property->id,
            'tenant_id'             => Auth::id(),
            'owner_id'              => $property->owner_id,
            'start_date'            => $startDate,
            'end_date'              => $endDate,
            'duration_months'       => $durationMonths,
            'monthly_amount'        => $monthlyAmount,
            'total_amount'          => $totalAmount,
            'deposit_amount'        => $depositAmount,
            'platform_commission'   => $commissions['platform'],
            'prospector_commission' => $commissions['prospector'],
            'tenant_message'        => $validated['message'],
            'referred_by_code'      => $referralCode,
            'referred_by_user_id'   => $referralUserId,
            'status'                => 'en_attente',
        ]);

        // Incrémenter clients apportés du démarcheur (si parrainage actif)
        if ($referralUserId) {
            \App\Models\User::where('id', $referralUserId)->increment('clients_brought');
            session()->forget(['referral_code', 'referral_user_id']);
        }

        // Notifier le propriétaire
        Notification::send(
            $property->owner,
            'booking_request',
            'Nouvelle demande de réservation',
            "Vous avez reçu une demande de réservation pour {$property->title}",
            route('dashboard.owner.bookings.show', $booking)
        );

        return redirect()->route('dashboard.tenant.bookings')
            ->with('success', 'Votre demande de réservation a été envoyée au propriétaire.');
    }

    /**
     * Afficher une réservation
     */
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['property', 'tenant', 'owner', 'payments']);

        return view('bookings.show', compact('booking'));
    }

    /**
     * Accepter une réservation (propriétaire)
     */
    public function accept(Booking $booking)
    {
        $this->authorize('respond', $booking);

        if (!$booking->isPending()) {
            return back()->with('error', 'Cette réservation ne peut plus être modifiée.');
        }

        $booking->update(['status' => 'acceptee']);

        // Notifier le locataire
        Notification::send(
            $booking->tenant,
            'booking_accepted',
            'Réservation acceptée !',
            "Votre demande de réservation pour {$booking->property->title} a été acceptée. Vous pouvez maintenant procéder au paiement.",
            route('bookings.payment', $booking)
        );

        return back()->with('success', 'Réservation acceptée. Le locataire a été notifié.');
    }

    /**
     * Refuser une réservation (propriétaire)
     */
    public function reject(Request $request, Booking $booking)
    {
        $this->authorize('respond', $booking);

        if (!$booking->isPending()) {
            return back()->with('error', 'Cette réservation ne peut plus être modifiée.');
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $booking->update([
            'status' => 'refusee',
            'rejection_reason' => $request->reason,
        ]);

        // Notifier le locataire
        Notification::send(
            $booking->tenant,
            'booking_rejected',
            'Réservation refusée',
            "Votre demande de réservation pour {$booking->property->title} a été refusée. Raison : {$request->reason}",
            route('dashboard.tenant.bookings')
        );

        return back()->with('success', 'Réservation refusée.');
    }

    /**
     * Annuler une réservation
     */
    public function cancel(Booking $booking)
    {
        $this->authorize('cancel', $booking);

        if (!$booking->canBeCancelled()) {
            return back()->with('error', 'Cette réservation ne peut plus être annulée.');
        }

        $booking->update(['status' => 'annulee']);

        // Notifier l'autre partie
        $otherUser = Auth::id() === $booking->tenant_id 
            ? $booking->owner 
            : $booking->tenant;

        Notification::send(
            $otherUser,
            'booking_rejected',
            'Réservation annulée',
            "La réservation pour {$booking->property->title} a été annulée.",
            route('dashboard')
        );

        return back()->with('success', 'Réservation annulée.');
    }

    /**
     * Page de paiement
     */
    public function payment(Booking $booking)
    {
        $this->authorize('pay', $booking);

        if ($booking->status !== 'acceptee') {
            return back()->with('error', 'Cette réservation ne peut pas être payée actuellement.');
        }

        $booking->load('property');
        $initialPayment = $booking->monthly_amount + $booking->deposit_amount;

        return view('bookings.payment', compact('booking', 'initialPayment'));
    }

    /**
     * Traiter le paiement (simulation)
     */
    public function processPayment(Request $request, Booking $booking)
    {
        $this->authorize('pay', $booking);

        $validated = $request->validate([
            'payment_method' => ['required', 'in:airtel_money,moov_money,gabon_telecom_cash'],
            'phone_number' => ['required', 'string', 'max:20'],
        ]);

        $initialPayment = $booking->monthly_amount + $booking->deposit_amount;

        // Créer le paiement
        $payment = $booking->payments()->create([
            'user_id' => Auth::id(),
            'amount' => $initialPayment,
            'payment_method' => $validated['payment_method'],
            'phone_number' => $validated['phone_number'],
            'payment_type' => 'initial',
            'status' => 'traitement',
            'description' => "Paiement initial - 1er mois + caution",
        ]);

        // Simuler une réponse de paiement réussie
        // En production, ici vous appelleriez l'API du fournisseur Mobile Money
        $payment->markAsConfirmed('TXN' . time() . rand(1000, 9999));

        // Mettre à jour la réservation
        $booking->update(['status' => 'payee']);

        // Si la propriété a un démarcheur, créer la commission
        if ($booking->property->hasProspector()) {
            Commission::create([
                'prospector_id' => $booking->property->prospector_id,
                'booking_id' => $booking->id,
                'property_id' => $booking->property_id,
                'amount' => $booking->prospector_commission,
                'percentage' => Booking::PROSPECTOR_COMMISSION_RATE * 100,
                'status' => 'validee',
            ]);

            // Notifier le démarcheur
            Notification::send(
                $booking->property->prospector,
                'commission_earned',
                'Commission gagnée !',
                "Vous avez gagné une commission de " . number_format($booking->prospector_commission, 0, ',', ' ') . " FCFA",
                route('dashboard.demarcheur.commissions')
            );
        }

        // Notifier le propriétaire
        Notification::send(
            $booking->owner,
            'payment_received',
            'Paiement reçu',
            "Le paiement pour {$booking->property->title} a été reçu.",
            route('dashboard.owner.bookings.show', $booking)
        );

        return redirect()->route('dashboard.tenant.bookings')
            ->with('success', 'Paiement effectué avec succès ! Votre location débutera le ' . $booking->start_date->format('d/m/Y'));
    }

    /**
     * Démarrer la location (auto ou manuel)
     */
    public function activate(Booking $booking)
    {
        if ($booking->status !== 'payee') {
            return back()->with('error', 'Cette réservation ne peut pas être activée.');
        }

        if ($booking->start_date->isFuture()) {
            return back()->with('error', 'La date de début n\'est pas encore atteinte.');
        }

        $booking->update(['status' => 'active']);
        $booking->property->update(['is_available' => false, 'status' => 'loue']);

        return back()->with('success', 'Location activée.');
    }

    /**
     * Terminer la location
     */
    public function complete(Booking $booking)
    {
        $this->authorize('complete', $booking);

        $booking->update(['status' => 'terminee']);
        $booking->property->update(['is_available' => true, 'status' => 'approuve']);

        return back()->with('success', 'Location terminée. Vous pouvez maintenant laisser un avis.');
    }
}

