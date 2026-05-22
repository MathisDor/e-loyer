<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->enum('termination_requested_by', ['locataire', 'proprietaire'])->nullable()->after('notes');
            $table->enum('termination_reason', [
                'fin_bail','non_paiement','nuisances',
                'depart_volontaire','mutation_professionnelle','achat_logement','autre',
            ])->nullable()->after('termination_requested_by');
            $table->text('termination_details')->nullable()->after('termination_reason');
            $table->timestamp('termination_requested_at')->nullable()->after('termination_details');
            $table->date('termination_effective_date')->nullable()->after('termination_requested_at');
            $table->enum('termination_status', ['en_attente','accepte','refuse','annule'])->nullable()->after('termination_effective_date');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'termination_requested_by','termination_reason','termination_details',
                'termination_requested_at','termination_effective_date','termination_status',
            ]);
        });
    }
};
