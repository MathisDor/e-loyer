<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Payment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ContractController extends Controller
{
    /**
     * Afficher un contrat
     */
    public function show(Contract $contract)
    {
        // Vérifier les permissions
        if ($contract->tenant_id !== Auth::id() && 
            $contract->owner_id !== Auth::id() && 
            !Auth::user()->isAdmin()) {
            abort(403);
        }

        $contract->load(['property', 'tenant', 'owner', 'payments', 'visit', 'inventoryReports']);

        // Déterminer le rôle de l'utilisateur
        $role = $this->resolveContractRole($contract);
        $tabs = $this->buildContractTabs($contract, $role);

        // Calculer les montants selon le rôle
        $baseRent = $contract->monthly_amount; // Loyer brut (sans commission ni frais)
        $commission = round($baseRent * 0.08); // 8% de commission
        $serviceFee = 400; // Frais de service fixes
        $totalRentWithFees = $baseRent + $commission + $serviceFee; // Loyer majoré pour le locataire

        // Montants à afficher selon le rôle
        if ($role === 'locataire') {
            $displayMonthlyAmount = $totalRentWithFees;
            $displayTotalAmount = $totalRentWithFees * $contract->duration_months;
        } else {
            // Propriétaire voit le loyer brut
            $displayMonthlyAmount = $baseRent;
            $displayTotalAmount = $baseRent * $contract->duration_months;
        }

        $view = match ($role) {
            'locataire' => 'contracts.show-tenant',
            'proprietaire' => 'contracts.show-owner',
            default => 'contracts.show',
        };

        return view($view, compact('contract', 'role', 'tabs', 'displayMonthlyAmount', 'displayTotalAmount', 'baseRent', 'commission', 'serviceFee', 'totalRentWithFees'));
    }

    /**
     * Détermine le contexte d'affichage du contrat pour l'utilisateur courant
     */
    protected function resolveContractRole(Contract $contract): string
    {
        $user = Auth::user();

        if ($user->id === $contract->tenant_id) {
            return 'locataire';
        }

        if ($user->id === $contract->owner_id) {
            return 'proprietaire';
        }

        if ($user->isAdmin()) {
            return 'admin';
        }

        return 'invite';
    }

    /**
     * Onglets à afficher selon le rôle
     */
    protected function buildContractTabs(Contract $contract, string $role): array
    {
        $tabs = [
            ['id' => 'overview', 'label' => 'Résumé'],
            ['id' => 'payments', 'label' => 'Paiements', 'badge' => $contract->months_paid . '/' . $contract->duration_months],
            ['id' => 'details', 'label' => 'Détails'],
        ];

        if ($contract->hasTerminationRequest() || $contract->status === 'resilie') {
            $tabs[] = ['id' => 'termination', 'label' => 'Résiliation', 'badge' => $contract->status === 'resilie' ? 'Résilié' : 'En cours'];
        }

        if ($role === 'locataire' && $contract->needsPayment()) {
            $tabs[] = ['id' => 'pay-rent', 'label' => 'Payer le loyer', 'badge' => 'Urgent'];
        }

        return $tabs;
    }

    /**
     * Payer le loyer mensuel
     */
    public function payMonthly(Request $request, Contract $contract)
    {
        if ($contract->tenant_id !== Auth::id()) {
            abort(403);
        }

        if (!$contract->needsPayment()) {
            return back()->with('error', 'Aucun paiement requis pour le moment.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:airtel_money,moov_money,gabon_telecom_cash'],
            'phone_number' => ['required', 'string', 'max:20'],
        ]);

        // Calculer le montant à payer (loyer majoré pour le locataire)
        $baseRent = $contract->monthly_amount;
        $commission = round($baseRent * 0.08); // 8% de commission
        $serviceFee = 400; // Frais de service fixes
        $amountToPay = $baseRent + $commission + $serviceFee;

        // Créer le paiement
        $payment = Payment::create([
            'contract_id' => $contract->id,
            'user_id' => Auth::id(),
            'amount' => $amountToPay,
            'payment_method' => $validated['payment_method'],
            'phone_number' => $validated['phone_number'],
            'payment_type' => 'mensuel',
            'status' => 'traitement',
            'description' => "Loyer mensuel (inclut commission et frais) - Mois " . ($contract->months_paid + 1) . "/6 - {$contract->property->title}",
        ]);

        // Simuler le paiement
        $payment->update([
            'status' => 'confirme',
            'transaction_id' => 'TXN' . time() . rand(1000, 9999),
            'paid_at' => now(),
        ]);

        // Mettre à jour le contrat
        $contract->increment('months_paid');
        $contract->update([
            'next_payment_date' => $contract->next_payment_date->addMonth(),
        ]);

        // Si tous les mois sont payés, terminer le contrat
        if ($contract->months_paid >= $contract->duration_months) {
            $contract->update([
                'status' => 'termine',
                'next_payment_date' => null,
            ]);

            // Rendre la propriété disponible
            $contract->property->update([
                'is_available' => true,
                'status' => 'approuve',
            ]);

            Notification::send(
                $contract->tenant,
                'contract_completed',
                'Contrat terminé',
                "Votre contrat de location pour {$contract->property->title} est terminé. Vous pouvez renouveler si vous le souhaitez.",
                route('contracts.show', $contract)
            );
        }

        // Notifier le propriétaire
        Notification::send(
            $contract->owner,
            'payment_received',
            'Paiement mensuel reçu',
            "Le paiement du mois " . $contract->months_paid . "/6 pour {$contract->property->title} a été reçu.",
            route('contracts.show', $contract)
        );

        return back()->with('success', 'Paiement effectué avec succès !');
    }

    /**
     * Demander la résiliation du contrat
     */
    public function requestTermination(Request $request, Contract $contract)
    {
        $user = Auth::user();

        if ($contract->tenant_id !== $user->id && $contract->owner_id !== $user->id) {
            abort(403);
        }

        if (!$contract->canRequestTermination($this->resolveContractRole($contract))) {
            return back()->with('error', 'Impossible de déposer une demande de résiliation pour ce contrat.');
        }

        $role = $contract->tenant_id === $user->id ? 'locataire' : 'proprietaire';
        $noticeDays = ($role === 'locataire' ? Contract::NOTICE_TENANT_MONTHS : Contract::NOTICE_OWNER_MONTHS) * 30;

        $validReasons = array_keys(Contract::TERMINATION_REASONS[$role]);

        $validated = $request->validate([
            'termination_reason'  => ['required', 'in:' . implode(',', $validReasons)],
            'termination_details' => ['required', 'string', 'min:20', 'max:1000'],
        ], [
            'termination_details.min' => 'Veuillez détailler votre demande (minimum 20 caractères).',
        ]);

        $effectiveDate = now()->addDays($noticeDays);

        $contract->update([
            'termination_requested_by'   => $role,
            'termination_reason'         => $validated['termination_reason'],
            'termination_details'        => $validated['termination_details'],
            'termination_requested_at'   => now(),
            'termination_effective_date' => $effectiveDate,
            'termination_status'         => 'en_attente',
        ]);

        // Notifier l'autre partie
        $other = $role === 'locataire' ? $contract->owner : $contract->tenant;
        $noticePeriod = $role === 'locataire' ? '1 mois' : '3 mois';

        Notification::send(
            $other,
            'termination_requested',
            'Demande de résiliation de contrat',
            ($role === 'locataire' ? 'Votre locataire' : 'Votre propriétaire') .
            " a déposé une demande de résiliation pour {$contract->property->title}. " .
            "Préavis de {$noticePeriod} — date effective : " . $effectiveDate->format('d/m/Y') . '.',
            route('contracts.show', $contract)
        );

        return back()->with('success', "Demande de résiliation envoyée. Préavis de {$noticePeriod}, effectif le " . $effectiveDate->format('d/m/Y') . '.');
    }

    /**
     * Accepter la résiliation (par la partie adverse)
     */
    public function acceptTermination(Contract $contract)
    {
        $user = Auth::user();

        if ($contract->tenant_id !== $user->id && $contract->owner_id !== $user->id) {
            abort(403);
        }

        if (!$contract->hasTerminationRequest()) {
            return back()->with('error', 'Aucune demande de résiliation en cours.');
        }

        $contract->update([
            'termination_status' => 'accepte',
            'status'             => 'resilie',
        ]);

        $contract->property->update([
            'is_available' => true,
            'status'       => 'approuve',
        ]);

        $requester = $contract->termination_requested_by === 'locataire'
            ? $contract->tenant
            : $contract->owner;

        Notification::send(
            $requester,
            'termination_accepted',
            'Résiliation acceptée',
            "Votre demande de résiliation pour {$contract->property->title} a été acceptée. " .
            "Date effective : " . $contract->termination_effective_date->format('d/m/Y') . '.',
            route('contracts.show', $contract)
        );

        return back()->with('success', 'Résiliation acceptée. Le contrat sera résilié le ' . $contract->termination_effective_date->format('d/m/Y') . '.');
    }

    /**
     * Annuler une demande de résiliation
     */
    public function cancelTermination(Contract $contract)
    {
        $user = Auth::user();

        if ($contract->tenant_id !== $user->id && $contract->owner_id !== $user->id) {
            abort(403);
        }

        $contract->update([
            'termination_requested_by'   => null,
            'termination_reason'         => null,
            'termination_details'        => null,
            'termination_requested_at'   => null,
            'termination_effective_date' => null,
            'termination_status'         => null,
        ]);

        return back()->with('success', 'Demande de résiliation annulée.');
    }

    /**
     * Renouveler un contrat
     */
    public function renew(Contract $contract)
    {
        if ($contract->tenant_id !== Auth::id() && $contract->owner_id !== Auth::id()) {
            abort(403);
        }

        if (!$contract->canRenew()) {
            return back()->with('error', 'Ce contrat ne peut pas être renouvelé.');
        }

        // Créer un nouveau contrat
        $newContract = Contract::create([
            'property_id' => $contract->property_id,
            'tenant_id' => $contract->tenant_id,
            'owner_id' => $contract->owner_id,
            'start_date' => $contract->end_date->copy()->addDay(),
            'end_date' => $contract->end_date->copy()->addMonths(6),
            'duration_months' => 6,
            'monthly_amount' => $contract->monthly_amount,
            'total_amount' => $contract->monthly_amount * 6,
            'deposit_amount' => $contract->deposit_amount,
            'status' => 'en_attente',
            'renewed_from_contract_id' => $contract->id,
            'next_payment_date' => $contract->end_date->copy()->addDay()->addMonth(),
        ]);

        // Marquer l'ancien contrat comme renouvelé
        $contract->update([
            'status' => 'renouvele',
            'can_renew' => false,
        ]);

        // Rendre la propriété indisponible
        $contract->property->update([
            'is_available' => false,
            'status' => 'loue',
        ]);

        // Notifier les deux parties
        Notification::send(
            $contract->tenant,
            'contract_renewed',
            'Contrat renouvelé',
            "Votre contrat pour {$contract->property->title} a été renouvelé pour 6 mois supplémentaires.",
            route('contracts.show', $newContract)
        );

        Notification::send(
            $contract->owner,
            'contract_renewed',
            'Contrat renouvelé',
            "Le contrat pour {$contract->property->title} a été renouvelé pour 6 mois supplémentaires.",
            route('contracts.show', $newContract)
        );

        return redirect()->route('contracts.show', $newContract)
            ->with('success', 'Contrat renouvelé avec succès pour 6 mois supplémentaires !');
    }
}

