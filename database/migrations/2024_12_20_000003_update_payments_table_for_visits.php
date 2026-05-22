<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Contournement pour SQLite (qui ne supporte pas bien le dropForeign via change())
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['booking_id']);
            });
            
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('booking_id')->nullable()->change();
                $table->foreignId('visit_id')->nullable()->after('booking_id')->constrained('visits')->onDelete('cascade');
            });
            
            // Recréer la contrainte foreign key pour booking_id
            Schema::table('payments', function (Blueprint $table) {
                $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            });
        } else {
             // SQLite ne supporte pas simplement de modifier la colonne.
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('visit_id')->nullable()->after('booking_id');
            });
        }
        
        // Modifier le type de paiement (nécessite une modification de colonne)
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_type ENUM('initial', 'mensuel', 'caution', 'remboursement', 'visite', 'premier_versement')");
        

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Contournement pour SQLite.
            if (DB::getDriverName() !== 'sqlite') {
                 $table->dropForeign(['visit_id']);
            }
            $table->dropColumn('visit_id');
            
            if (DB::getDriverName() !== 'sqlite') {
                // Optionnel: remettre nullable(false) si nécessaire
                // $table->foreignId('booking_id')->nullable(false)->change();
            }
        });
    }
};

