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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospector_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            
            // Montant
            $table->decimal('amount', 15, 2);
            $table->decimal('percentage', 5, 2)->default(5.00);
            
            // Statut
            $table->enum('status', [
                'en_attente',
                'validee',
                'payee'
            ])->default('en_attente');
            
            // Paiement
            $table->timestamp('paid_at')->nullable();
            $table->enum('payment_method', [
                'airtel_money',
                'moov_money',
                'gabon_telecom_cash',
                'virement'
            ])->nullable();
            $table->string('transaction_id')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['prospector_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};


