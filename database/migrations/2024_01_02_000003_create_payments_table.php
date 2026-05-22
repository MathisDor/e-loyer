<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Montant et méthode
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', [
                'airtel_money',
                'moov_money',
                'gabon_telecom_cash',
                'carte_bancaire'
            ]);
            
            // Transaction
            $table->string('transaction_id')->nullable();
            $table->string('phone_number')->nullable();
            
            // Type de paiement
            $table->enum('payment_type', [
                'initial',        // Premier mois + caution
                'mensuel',        // Loyer mensuel
                'caution',        // Caution seule
                'remboursement'   // Remboursement caution
            ]);
            
            // Statut
            $table->enum('status', [
                'en_attente',
                'traitement',
                'confirme',
                'echoue',
                'rembourse'
            ])->default('en_attente');
            
            $table->text('description')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable(); // Données supplémentaires de l'API
            
            $table->timestamps();
            
            // Index
            $table->index(['booking_id', 'status']);
            $table->index(['transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};


