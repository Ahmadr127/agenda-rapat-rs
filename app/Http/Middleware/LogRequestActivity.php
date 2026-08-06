<?php

namespace App\Http\Middleware;

use App\Support\Logging\RequestLogContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        RequestLogContext::initializeRequest($request);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        Log::info('Request completed', RequestLogContext::requestCompleted($request, $response));
    }
}
