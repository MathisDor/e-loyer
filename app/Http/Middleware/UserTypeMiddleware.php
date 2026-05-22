<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserTypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$types): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Bloquer les comptes suspendus
        if ($request->user()->is_suspended) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Votre compte a été suspendu. Raison : ' . ($request->user()->suspension_reason ?? 'Non précisée') . '. Contactez le support.');
        }

        if (!in_array($request->user()->user_type, $types)) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}


