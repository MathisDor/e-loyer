<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Notification;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    /**
     * Liste des commissions
     */
    public function index(Request $request)
    {
        $query = Commission::with(['prospector', 'property', 'booking']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $commissions = $query->latest()->paginate(20);

        // Statistiques
        $stats = [
            'pending' => Commission::pending()->sum('amount'),
            'validated' => Commission::validated()->sum('amount'),
            'paid' => Commission::paid()->sum('amount'),
        ];

        return view('admin.commissions.index', compact('commissions', 'stats'));
    }

    /**
     * Marquer une commission comme payée
     */
    public function pay(Request $request, Commission $commission)
    {
        $request->validate([
            'payment_method' => ['required', 'in:airtel_money,moov_money,gabon_telecom_cash,virement'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
        ]);

        $commission->markAsPaid(
            $request->payment_method,
            $request->transaction_id
        );

        // Notifier le démarcheur
        Notification::send(
            $commission->prospector,
            'commission_paid',
            'Commission payée',
            "Votre commission de {$commission->formatted_amount} a été versée.",
            route('dashboard.demarcheur.commissions')
        );

        return back()->with('success', 'Commission marquée comme payée.');
    }

    /**
     * Payer plusieurs commissions
     */
    public function payBulk(Request $request)
    {
        $request->validate([
            'commission_ids' => ['required', 'array'],
            'commission_ids.*' => ['exists:commissions,id'],
            'payment_method' => ['required', 'in:airtel_money,moov_money,gabon_telecom_cash,virement'],
        ]);

        $commissions = Commission::whereIn('id', $request->commission_ids)
            ->where('status', 'validee')
            ->get();

        foreach ($commissions as $commission) {
            $commission->markAsPaid($request->payment_method);

            Notification::send(
                $commission->prospector,
                'commission_paid',
                'Commission payée',
                "Votre commission de {$commission->formatted_amount} a été versée.",
                route('dashboard.demarcheur.commissions')
            );
        }

        return back()->with('success', count($commissions) . ' commissions payées.');
    }
}


