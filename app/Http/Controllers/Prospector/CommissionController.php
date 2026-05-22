<?php

namespace App\Http\Controllers\Prospector;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionController extends Controller
{
    /**
     * Liste des commissions du démarcheur
     */
    public function index(Request $request)
    {
        $query = Auth::user()->commissions()
            ->with(['property', 'booking']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $commissions = $query->latest()->paginate(15);

        $stats = [
            'pending' => Auth::user()->commissions()->pending()->sum('amount'),
            'validated' => Auth::user()->commissions()->validated()->sum('amount'),
            'paid' => Auth::user()->commissions()->paid()->sum('amount'),
            'total_earnings' => Auth::user()->total_earnings,
        ];

        return view('prospector.commissions.index', compact('commissions', 'stats'));
    }

    /**
     * Historique des paiements de commissions
     */
    public function history()
    {
        $paidCommissions = Auth::user()->commissions()
            ->paid()
            ->with(['property', 'booking'])
            ->latest('paid_at')
            ->paginate(15);

        return view('prospector.commissions.history', compact('paidCommissions'));
    }
}


