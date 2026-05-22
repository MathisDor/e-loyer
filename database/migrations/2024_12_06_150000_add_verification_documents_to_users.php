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
        Schema::table('users', function (Blueprint $table) {
            // Documents de vérification pour locataires
            $table->string('pay_slip')->nullable()->after('id_card');
            $table->string('employment_contract')->nullable()->after('pay_slip');
            $table->string('proof_of_address')->nullable()->after('employment_contract');
            $table->string('bank_statement')->nullable()->after('proof_of_address');
            
            // Documents pour propriétaires/agences
            $table->string('property_title')->nullable()->after('bank_statement');
            $table->string('business_registration')->nullable()->after('property_title');
            
            // Vérification téléphone
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'pay_slip',
                'employment_contract',
                'proof_of_address',
                'bank_statement',
                'property_title',
                'business_registration',
                'phone_verified_at',
            ]);
        });
    }
};


