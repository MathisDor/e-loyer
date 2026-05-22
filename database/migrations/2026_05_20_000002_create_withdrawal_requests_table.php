<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['airtel_money', 'moov_money', 'gabon_telecom_cash', 'virement']);
            $table->string('phone_number', 20)->nullable();
            $table->string('account_name')->nullable();
            $table->enum('status', ['en_attente', 'approuve', 'paye', 'rejete'])->default('en_attente');
            $table->string('transaction_id')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
