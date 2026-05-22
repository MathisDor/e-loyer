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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            
            // Dates et durée
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_months');
            
            // Montants
            $table->decimal('monthly_amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('deposit_amount', 15, 2);
            
            // Commissions
            $table->decimal('platform_commission', 15, 2)->default(0); // 12%
            $table->decimal('prospector_commission', 15, 2)->default(0); // 5%
            
            // Statut
            $table->enum('status', [
                'en_attente',      // Demande envoyée
                'acceptee',        // Propriétaire a accepté
                'refusee',         // Propriétaire a refusé
                'payee',           // Premier paiement effectué
                'active',          // Location en cours
                'terminee',        // Location terminée
                'annulee'          // Annulation
            ])->default('en_attente');
            
            $table->text('tenant_message')->nullable(); // Message du locataire
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['status', 'start_date']);
            $table->index(['tenant_id', 'status']);
            $table->index(['owner_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};


