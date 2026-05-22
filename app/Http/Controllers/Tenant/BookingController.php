<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Liste des réservations du locataire
     */
    public function index(Request $request)
    {
        $query = Booking::with(['property', 'owner', 'payments'])
            ->forTenant(Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);

        return view('tenant.bookings.index', compact('bookings'));
    }

    /**
     * Afficher une réservation
     */
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['property', 'owner', 'payments']);

        return view('tenant.bookings.show', compact('booking'));
    }

    /**
     * Historique des paiements
     */
    public function payments()
    {
        $payments = Payment::with(['booking.property', 'visit.property', 'contract.property'])
            ->forUser(Auth::id())
            ->latest()
            ->paginate(15);

        $stats = [
            'total_paid' => Payment::forUser(Auth::id())->confirmed()->sum('amount'),
            'pending' => Payment::forUser(Auth::id())->pending()->sum('amount'),
        ];

        return view('tenant.payments.index', compact('payments', 'stats'));
    }

    /**
     * Payer un loyer mensuel
     */
    public function payRent(Request $request, Booking $booking)
    {
        $this->authorize('pay', $booking);

        if (!in_array($booking->status, ['active', 'payee'])) {
            return back()->with('error', 'Impossible de payer le loyer pour cette réservation.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:airtel_money,moov_money,gabon_telecom_cash'],
            'phone_number' => ['required', 'string', 'max:20'],
        ]);

        // Créer le paiement
        $payment = $booking->payments()->create([
            'user_id' => Auth::id(),
            'amount' => $booking->monthly_amount,
            'payment_method' => $validated['payment_method'],
            'phone_number' => $validated['phone_number'],
            'payment_type' => 'mensuel',
            'status' => 'traitement',
            'description' => 'Loyer mensuel - ' . now()->format('F Y'),
        ]);

        // Simuler le paiement (en production, appeler l'API Mobile Money)
        $payment->markAsConfirmed('TXN' . time() . rand(1000, 9999));

        return back()->with('success', 'Paiement effectué avec succès !');
    }
}

