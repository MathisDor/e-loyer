<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\Notification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendMonthlyPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:send-payment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoyer des rappels de paiement mensuel pour les contrats actifs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Envoi des rappels de paiement mensuel...');

        // Contrats actifs qui nécessitent un paiement
        $contracts = Contract::where('status', 'actif')
            ->where('next_payment_date', '<=', now())
            ->whereColumn('months_paid', '<', 'duration_months')
            ->with(['tenant', 'property'])
            ->get();

        $count = 0;

        foreach ($contracts as $contract) {
            // Vérifier si le paiement n'a pas déjà été effectué ce mois
            $hasPaymentThisMonth = $contract->payments()
                ->where('payment_type', 'mensuel')
                ->where('status', 'confirme')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->exists();

            if (!$hasPaymentThisMonth) {
                Notification::send(
                    $contract->tenant,
                    'payment_due',
                    'Paiement mensuel dû',
                    "Le paiement du loyer pour {$contract->property->title} est dû. Montant : {$contract->formatted_monthly_amount}",
                    route('contracts.show', $contract)
                );

                $count++;
            }
        }

        $this->info("{$count} rappels de paiement envoyés.");

        return Command::SUCCESS;
    }
}


