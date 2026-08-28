<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDoctorOnboardingComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $doctor = Auth::guard('doctor')->user();

        if (! $doctor) {
            return $next($request);
        }

        if (in_array($doctor->status, ['rejected', 'suspended'], true)) {
            return $request->routeIs('doctor.blocked')
                ? $next($request)
                : redirect()->route('doctor.blocked');
        }

        if ($doctor->onboarding_step === 'basic_info_done') {
            return $request->routeIs('doctor.onboarding.*')
                ? $next($request)
                : redirect()->route('doctor.onboarding.edit');
        }

        if ($doctor->status === 'pending_approval') {
            return $request->routeIs('doctor.pending')
                ? $next($request)
                : redirect()->route('doctor.pending');
        }

        return $next($request);
    }
}
