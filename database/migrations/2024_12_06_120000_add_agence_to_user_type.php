<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite ne supporte pas ALTER COLUMN pour les enums
        // On doit recréer la colonne
        
        if (DB::connection()->getDriverName() === 'sqlite') {
            // Pour SQLite, on doit modifier directement avec des requêtes SQL
            // D'abord, supprimer la contrainte CHECK
            DB::statement('PRAGMA foreign_keys=off');
            
            // Créer une table temporaire
            DB::statement('CREATE TABLE users_temp AS SELECT * FROM users');
            
            // Supprimer la table originale
            Schema::drop('users');
            
            // Recréer la table avec le bon enum
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('agency_name')->nullable();
                $table->text('agency_description')->nullable();
                $table->string('agency_logo')->nullable();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('user_type')->default('locataire'); // Changé en string pour éviter les problèmes
                $table->string('phone')->nullable();
                $table->string('whatsapp')->nullable();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('id_card')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->decimal('commission_rate', 5, 2)->default(5.00);
                $table->decimal('total_earnings', 15, 2)->default(0.00);
                $table->decimal('balance', 12, 2)->default(0.00);
                $table->string('avatar')->nullable();
                $table->text('bio')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
            
            // Copier les données
            DB::statement('INSERT INTO users (id, name, email, email_verified_at, password, user_type, phone, whatsapp, address, city, id_card, is_verified, commission_rate, total_earnings, avatar, bio, remember_token, created_at, updated_at, deleted_at) SELECT id, name, email, email_verified_at, password, user_type, phone, whatsapp, address, city, id_card, is_verified, commission_rate, total_earnings, avatar, bio, remember_token, created_at, updated_at, deleted_at FROM users_temp');
            
            // Supprimer la table temporaire
            DB::statement('DROP TABLE users_temp');
            
            DB::statement('PRAGMA foreign_keys=on');
        } else {
            // Pour MySQL/PostgreSQL
            Schema::table('users', function (Blueprint $table) {
                $table->string('user_type')->default('locataire')->change();
            });
        }
    }

    public function down(): void
    {
        // Pas de rollback pour cette migration
    }
};


