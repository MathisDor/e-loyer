<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter ref_code et badge_level sur les users
        Schema::table('users', function (Blueprint $table) {
            $table->string('ref_code', 12)->nullable()->unique()->after('commission_rate');
            $table->string('badge_level', 20)->default('bronze')->after('ref_code'); // bronze, silver, gold, platinum
            $table->integer('clients_brought')->default(0)->after('badge_level');
            $table->integer('locations_concluded')->default(0)->after('clients_brought');
        });

        // Ajouter referred_by_code sur les bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('referred_by_code', 12)->nullable()->after('tenant_message');
            $table->unsignedBigInteger('referred_by_user_id')->nullable()->after('referred_by_code');
            $table->foreign('referred_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['referred_by_user_id']);
            $table->dropColumn(['referred_by_code', 'referred_by_user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ref_code', 'badge_level', 'clients_brought', 'locations_concluded']);
        });
    }
};
