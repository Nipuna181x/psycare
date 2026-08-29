<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicStaffIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::guard('clinic_staff')->user();

        if ($staff && $staff->status !== 'active') {
            Auth::guard('clinic_staff')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('medical-center.login')
                ->withErrors(['email' => 'Your staff access has been removed.']);
        }

        return $next($request);
    }
}
