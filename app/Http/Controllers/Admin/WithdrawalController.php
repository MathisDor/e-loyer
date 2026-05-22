<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    public function index()
    {
        $pending = WithdrawalRequest::with('user')->pending()->latest()->get();
        $processed = WithdrawalRequest::with('user')
            ->whereIn('status', ['paye', 'rejete', 'approuve'])
            ->latest('processed_at')
            ->take(50)
            ->get();

        $stats = [
            'pending_count'  => $pending->count(),
            'pending_amount' => $pending->sum('amount'),
            'paid_total'     => WithdrawalRequest::where('status', 'paye')->sum('amount'),
        ];

        return view('admin.withdrawals.index', compact('pending', 'processed', 'stats'));
    }

    public function approve(Request $request, WithdrawalRequest $withdrawal)
    {
        $validated = $request->validate([
            'transaction_id' => ['required', 'string', 'max:100'],
        ]);

        if (!$withdrawal->isPending()) {
            return back()->with('error', 'Cette demande ne peut plus être traitée.');
        }

        $withdrawal->approve(Auth::user(), $validated['transaction_id']);

        Notification::send(
            $withdrawal->user,
            'withdrawal_paid',
            'Retrait effectué !',
            "Votre retrait de " . $withdrawal->formatted_amount . " a été traité avec succès.",
            route('dashboard.demarcheur.withdrawals')
        );

        return back()->with('success', 'Retrait approuvé et payé.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        if (!$withdrawal->isPending()) {
            return back()->with('error', 'Cette demande ne peut plus être traitée.');
        }

        $withdrawal->reject(Auth::user(), $validated['rejection_reason']);

        Notification::send(
            $withdrawal->user,
            'withdrawal_rejected',
            'Retrait refusé',
            "Votre demande de retrait de " . $withdrawal->formatted_amount . " a été refusée : " . $validated['rejection_reason'],
            route('dashboard.demarcheur.withdrawals')
        );

        return back()->with('success', 'Demande refusée.');
    }
}
