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
        Schema::table('properties', function (Blueprint $table) {
            // Prix de la visite
            if (!Schema::hasColumn('properties', 'visit_price')) {
                $table->decimal('visit_price', 15, 2)->nullable()->after('deposit');
            }
            
            // Si caution requise ou pas
            if (!Schema::hasColumn('properties', 'requires_deposit')) {
                $table->boolean('requires_deposit')->default(true)->after('visit_price');
            }
            
            // À qui est assignée la visite
            if (!Schema::hasColumn('properties', 'visit_assigned_to')) {
                $table->enum('visit_assigned_to', ['self', 'agence', 'demarcheur'])->default('self')->after('requires_deposit');
            }
            
            // ID de l'utilisateur assigné (agence ou démarcheur)
            if (!Schema::hasColumn('properties', 'visit_assigned_user_id')) {
                $table->foreignId('visit_assigned_user_id')->nullable()->constrained('users')->onDelete('set null')->after('visit_assigned_to');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['visit_assigned_user_id']);
            $table->dropColumn(['visit_price', 'requires_deposit', 'visit_assigned_to', 'visit_assigned_user_id']);
        });
    }
};

