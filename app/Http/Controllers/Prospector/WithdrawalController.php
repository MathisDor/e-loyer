<?php

namespace App\Http\Controllers\Prospector;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    /**
     * Liste des demandes de retrait du démarcheur.
     */
    public function index()
    {
        $user = Auth::user();

        $withdrawals = $user->withdrawalRequests()->latest()->paginate(15);

        $stats = [
            'balance'        => (float) $user->total_earnings - $user->withdrawalRequests()->whereIn('status', ['approuve', 'paye'])->sum('amount'),
            'pending_amount' => $user->withdrawalRequests()->pending()->sum('amount'),
            'paid_amount'    => $user->withdrawalRequests()->where('status', 'paye')->sum('amount'),
            'total_earnings' => (float) $user->total_earnings,
        ];

        return view('prospector.withdrawals.index', compact('withdrawals', 'stats'));
    }

    /**
     * Créer une nouvelle demande de retrait.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $availableBalance = (float) $user->total_earnings
            - $user->withdrawalRequests()->whereIn('status', ['en_attente', 'approuve', 'paye'])->sum('amount');

        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:5000', "max:{$availableBalance}"],
            'payment_method' => ['required', 'in:' . implode(',', array_keys(WithdrawalRequest::METHODS))],
            'phone_number'   => ['required_unless:payment_method,virement', 'nullable', 'string', 'max:20'],
            'account_name'   => ['required', 'string', 'max:100'],
        ], [
            'amount.min'   => 'Le montant minimum est de 5 000 FCFA.',
            'amount.max'   => "Solde insuffisant. Disponible : " . number_format($availableBalance, 0, ',', ' ') . " FCFA.",
        ]);

        // Empêcher une 2e demande en attente
        if ($user->withdrawalRequests()->pending()->exists()) {
            return back()->with('error', 'Vous avez déjà une demande de retrait en attente.');
        }

        WithdrawalRequest::create([
            'user_id'        => $user->id,
            'amount'         => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'phone_number'   => $validated['phone_number'] ?? null,
            'account_name'   => $validated['account_name'],
            'status'         => 'en_attente',
        ]);

        return back()->with('success', 'Votre demande de retrait a été soumise. Elle sera traitée sous 24-48h.');
    }

    /**
     * Annuler une demande en attente.
     */
    public function cancel(WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->user_id !== Auth::id() || !$withdrawal->isPending()) {
            abort(403);
        }

        $withdrawal->update(['status' => 'rejete', 'rejection_reason' => 'Annulée par le démarcheur.']);

        return back()->with('success', 'Demande annulée.');
    }
}
