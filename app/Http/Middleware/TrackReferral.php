<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackReferral
{
    /**
     * Mémoriser le code de parrainage (?ref=DM1234XY) en session.
     * La session est persistée 30 jours via un cookie.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->filled('ref')) {
            $refCode = strtoupper(trim($request->query('ref')));

            // Vérifier que le code correspond bien à un démarcheur actif
            $prospector = User::where('ref_code', $refCode)
                ->where('user_type', 'demarcheur')
                ->whereNull('is_suspended')
                ->orWhere('is_suspended', false)
                ->first();

            if ($prospector) {
                // Stocker dans la session (dure toute la session)
                session(['referral_code' => $refCode, 'referral_user_id' => $prospector->id]);

                // Incrémenter le compteur de partages du démarcheur (1 fois par IP par jour)
                $cacheKey = "ref_click_{$refCode}_" . $request->ip();
                if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                    \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addDay());
                }
            }
        }

        return $next($request);
    }
}
