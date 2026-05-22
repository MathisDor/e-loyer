<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plans d'abonnement
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Starter, Pro, Enterprise
            $table->string('slug')->unique();
            $table->decimal('price', 12, 2); // Prix mensuel
            $table->integer('max_properties')->default(5); // Nombre max de biens
            $table->integer('max_images_per_property')->default(5); // Images par bien
            $table->boolean('can_sponsor')->default(false); // Peut sponsoriser
            $table->integer('sponsor_discount')->default(0); // Réduction sur sponsoring (%)
            $table->boolean('priority_support')->default(false);
            $table->boolean('analytics_advanced')->default(false);
            $table->text('features')->nullable(); // JSON des fonctionnalités
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Abonnements des agences
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->enum('status', ['active', 'cancelled', 'expired', 'pending'])->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
        });

        // Sponsorisations de biens
        Schema::create('sponsorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['boost', 'featured', 'premium'])->default('boost');
            $table->decimal('amount', 12, 2);
            $table->integer('duration_days')->default(7);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->integer('views_count')->default(0);
            $table->integer('clicks_count')->default(0);
            $table->timestamps();
        });

        // Méthodes de paiement
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['airtel_money', 'moov_money', 'bank_transfer', 'card']);
            $table->string('name'); // Nom du compte/carte
            $table->string('phone')->nullable(); // Pour Mobile Money
            $table->string('account_number')->nullable(); // Pour virement
            $table->string('bank_name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        // Transactions
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['subscription', 'sponsorship', 'deposit', 'withdrawal', 'commission', 'refund']);
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('reference')->unique();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Demandes de retrait
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_method_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->decimal('fee', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
            $table->string('reference')->unique();
            $table->text('reject_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // Ajouter colonne balance au user
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 12, 2)->default(0)->after('total_earnings');
            $table->string('agency_name')->nullable()->after('name');
            $table->text('agency_description')->nullable()->after('agency_name');
            $table->string('agency_logo')->nullable()->after('agency_description');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['balance', 'agency_name', 'agency_description', 'agency_logo']);
        });
        
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('sponsorships');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};

