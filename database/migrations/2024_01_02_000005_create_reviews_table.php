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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewed_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('cascade');
            
            // Notation
            $table->tinyInteger('rating')->unsigned(); // 1-5
            $table->text('comment')->nullable();
            
            // Type d'avis
            $table->enum('type', [
                'property',       // Avis sur le bien
                'owner',          // Avis sur le propriétaire
                'tenant'          // Avis sur le locataire
            ]);
            
            // Modération
            $table->boolean('is_approved')->default(false);
            $table->text('moderation_note')->nullable();
            
            $table->timestamps();
            
            // Un seul avis par type par réservation
            $table->unique(['booking_id', 'reviewer_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};


