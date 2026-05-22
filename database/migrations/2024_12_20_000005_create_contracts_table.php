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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->nullable()->constrained('visits')->onDelete('set null');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            
            // Durée du contrat (toujours 6 mois)
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_months')->default(6);
            
            // Montants
            $table->decimal('monthly_amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('deposit_amount', 15, 2)->nullable();
            
            // Statut du contrat
            $table->enum('status', [
                'en_attente',      // En attente de signature
                'signe',           // Signé par les deux parties
                'actif',           // Contrat actif (paiements en cours)
                'termine',         // Contrat terminé
                'renouvele',       // Contrat renouvelé
                'resilie'          // Contrat résilié
            ])->default('en_attente');
            
            // Signatures
            $table->timestamp('tenant_signed_at')->nullable();
            $table->timestamp('owner_signed_at')->nullable();
            
            // Paiements
            $table->integer('months_paid')->default(0); // Nombre de mois payés
            $table->date('next_payment_date')->nullable(); // Date du prochain paiement
            
            // Renouvellement
            $table->foreignId('renewed_from_contract_id')->nullable()->constrained('contracts')->onDelete('set null');
            $table->boolean('can_renew')->default(true);
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['property_id', 'status']);
            $table->index(['tenant_id', 'status']);
            $table->index(['owner_id', 'status']);
            $table->index(['next_payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};


