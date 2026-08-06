<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireManagerIt
{
    public function handle(Request $request, Closure $next): Response
    {
        $employee = Auth::user()?->employee;

        if (! $employee || $employee->job_position !== 'MANAGER IT') {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk MANAGER IT.');
        }

        return $next($request);
    }
}
