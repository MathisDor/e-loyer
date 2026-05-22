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
        Schema::create('inventory_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['entree', 'sortie'])->default('entree');
            $table->date('report_date');
            $table->text('observations')->nullable();
            $table->json('items')->nullable(); // Liste des éléments vérifiés avec leur état
            $table->json('photos')->nullable(); // URLs des photos
            $table->boolean('tenant_signed')->default(false);
            $table->boolean('owner_signed')->default(false);
            $table->timestamp('tenant_signed_at')->nullable();
            $table->timestamp('owner_signed_at')->nullable();
            $table->text('tenant_notes')->nullable();
            $table->text('owner_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_reports');
    }
};

