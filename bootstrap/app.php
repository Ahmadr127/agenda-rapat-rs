<?php

use App\Http\Middleware\LogRequestActivity;
use App\Support\Logging\RequestLogContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(LogRequestActivity::class);
        $middleware->alias([
            'manager_it' => \App\Http\Middleware\RequireManagerIt::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->context(function (Throwable $exception, array $context): array {
            return RequestLogContext::exceptionContext(
                RequestLogContext::currentRequest(),
                $exception,
            );
        });
    })->create();
