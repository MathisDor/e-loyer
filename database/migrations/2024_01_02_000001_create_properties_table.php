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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('prospector_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Informations de base
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['appartement', 'maison', 'studio', 'villa', 'chambre'])->default('appartement');
            
            // Caractéristiques
            $table->integer('bedrooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->decimal('surface', 10, 2)->nullable(); // m²
            
            // Prix
            $table->decimal('monthly_price', 15, 2); // FCFA
            $table->decimal('deposit', 15, 2)->nullable(); // Caution
            
            // Localisation
            $table->string('address');
            $table->string('city');
            $table->string('neighborhood')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Équipements (stockés en JSON)
            $table->json('amenities')->nullable();
            
            // Images (stockées en JSON)
            $table->json('images')->nullable();
            
            // Statuts
            $table->enum('status', ['en_attente', 'approuve', 'rejete', 'loue'])->default('en_attente');
            $table->boolean('is_available')->default(true);
            $table->boolean('prospector_validated')->default(false); // Validation par propriétaire si ajouté par démarcheur
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour recherche
            $table->index(['city', 'type', 'status']);
            $table->index(['monthly_price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};


