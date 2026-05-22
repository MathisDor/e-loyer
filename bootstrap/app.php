<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Symfony\Component\HttpKernel\Exception\RequestEntityTooLargeHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'user.type' => \App\Http\Middleware\UserTypeMiddleware::class,
        ]);

        // Tracker le code de parrainage (?ref=xxx) sur toutes les pages web
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackReferral::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Réponse claire pour les requêtes trop volumineuses (images, formulaires)
        $exceptions->render(function (PostTooLargeException|RequestEntityTooLargeHttpException $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'La requête est trop volumineuse. Merci de réduire la taille des fichiers (ex: compresser les images).',
                ], 413);
            }

            return response()->view('errors.413', [], 413);
        });
    })->create();
