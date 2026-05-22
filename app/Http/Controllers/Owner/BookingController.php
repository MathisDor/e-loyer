<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Liste des réservations du propriétaire
     */
    public function index(Request $request)
    {
        $query = Booking::with(['property', 'tenant', 'payments'])
            ->forOwner(Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);

        $stats = [
            'pending' => Booking::forOwner(Auth::id())->pending()->count(),
            'active' => Booking::forOwner(Auth::id())->whereIn('status', ['active', 'payee'])->count(),
            'total_revenue' => Booking::forOwner(Auth::id())
                ->whereIn('status', ['active', 'payee', 'terminee'])
                ->sum('monthly_amount'),
        ];

        return view('owner.bookings.index', compact('bookings', 'stats'));
    }

    /**
     * Afficher une réservation
     */
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['property', 'tenant', 'payments', 'reviews']);

        return view('owner.bookings.show', compact('booking'));
    }

    /**
     * Calendrier des locations
     */
    public function calendar()
    {
        $bookings = Booking::with('property')
            ->forOwner(Auth::id())
            ->whereIn('status', ['active', 'payee', 'acceptee'])
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'title' => $booking->property->title,
                    'start' => $booking->start_date->format('Y-m-d'),
                    'end' => $booking->end_date->format('Y-m-d'),
                    'color' => match($booking->status) {
                        'active' => '#009639',
                        'payee' => '#3A75C4',
                        'acceptee' => '#FCD116',
                        default => '#6B7280',
                    },
                    'url' => route('dashboard.owner.bookings.show', $booking),
                ];
            });

        return view('owner.bookings.calendar', compact('bookings'));
    }
}


