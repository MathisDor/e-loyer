<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modifier l'enum status pour ajouter 'annulee'
        Schema::table('commissions', function (Blueprint $table) {
            $table->string('status')->default('en_attente')->change();
        });
    }

    public function down(): void {}
};
