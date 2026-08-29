<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrimaryClinicAccount
{
    /**
     * Handle an incoming request. Only the primary medical_center login may
     * manage clinic staff accounts — clinic_staff sessions are blocked.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Auth::guard('medical_center')->check(), 403);

        return $next($request);
    }
}
