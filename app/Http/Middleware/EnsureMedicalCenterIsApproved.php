<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMedicalCenterIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $medicalCenter = Auth::guard('medical_center')->user();

        if ($medicalCenter && $medicalCenter->status !== 'approved') {
            Auth::guard('medical_center')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('medical-center.login')
                ->withErrors(['email' => 'Your medical center account is no longer approved to access the dashboard.']);
        }

        return $next($request);
    }
}
