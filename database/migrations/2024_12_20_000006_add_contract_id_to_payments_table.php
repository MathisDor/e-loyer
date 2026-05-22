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
        // visit_id may be missing if the previous migration failed silently
        if (!Schema::hasColumn('payments', 'visit_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('visit_id')->nullable()->after('booking_id')->constrained('visits')->onDelete('cascade');
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['contract_id']);
            $table->dropColumn('contract_id');
        });
    }
};


