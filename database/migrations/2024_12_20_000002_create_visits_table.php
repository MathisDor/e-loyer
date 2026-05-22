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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null'); // Agence ou démarcheur
            
            // Date et heure de la visite
            $table->dateTime('scheduled_at');
            
            // Montants
            $table->decimal('base_price', 15, 2); // Prix de base de la visite
            $table->decimal('commission', 15, 2); // 8% de commission
            $table->decimal('service_fee', 15, 2)->default(400); // Frais de service fixes
            $table->decimal('total_amount', 15, 2); // Total à payer
            
            // Statut de la visite
            $table->enum('status', [
                'reservee',      // Réservée et payée
                'en_cours',       // En cours (démarcheur avec locataire)
                'terminee',       // Terminée
                'acceptee',       // Locataire a accepté la propriété
                'refusee',        // Locataire a refusé la propriété
                'annulee',        // Annulée
                'non_effectuee'   // Visite non effectuée (absence locataire)
            ])->default('reservee');
            
            // Validation par le démarcheur
            $table->enum('visit_status', ['en_attente', 'reussie', 'non_effectuee'])->default('en_attente');
            $table->text('visit_status_notes')->nullable(); // Notes du démarcheur
            
            // Acceptation/refus de la propriété
            $table->boolean('property_accepted')->nullable();
            $table->text('refusal_reason')->nullable();
            
            // Paiement
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            
            $table->text('notes')->nullable(); // Notes du démarcheur ou locataire
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['property_id', 'status']);
            $table->index(['tenant_id', 'status']);
            $table->index(['scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};

