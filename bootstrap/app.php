<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Modules\Employees\Models\Employee;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Catch NotFoundHttpException which wraps ModelNotFoundException
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // Check if this 404 was triggered by Route Model Binding
                if ($e->getPrevious() instanceof ModelNotFoundException) {
                    $modelClass = $e->getPrevious()->getModel();

                    // Specific message if an Employee model failed binding
                    if ($modelClass === Employee::class) {
                        return response()->json([
                            'message' => 'The required employee is not in the system.',
                        ], 404);
                    }

                    // Fallback generic message for other models
                    return response()->json([
                        'message' => 'The requested resource was not found.',
                    ], 404);
                }

                // Standard 404 route not found response
                return response()->json([
                    'message' => 'Route or resource not found.',
                ], 404);
            }
        });

    })->create();
