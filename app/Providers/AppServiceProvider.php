<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Property;
use App\Policies\BookingPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\PropertyPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrer les policies
        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);

        // Directives Blade personnalisées
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        Blade::if('proprietaire', function () {
            return auth()->check() && auth()->user()->isProprietaire();
        });

        Blade::if('locataire', function () {
            return auth()->check() && auth()->user()->isLocataire();
        });

        Blade::if('demarcheur', function () {
            return auth()->check() && auth()->user()->isDemarcheur();
        });

        Blade::if('verified', function () {
            return auth()->check() && auth()->user()->is_verified;
        });
    }
}
