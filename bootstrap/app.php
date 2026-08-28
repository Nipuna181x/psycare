<?php

use App\Http\Middleware\EnsureDoctorOnboardingComplete;
use App\Http\Middleware\EnsureMedicalCenterIsApproved;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'medical_center.approved' => EnsureMedicalCenterIsApproved::class,
            'doctor.onboarding' => EnsureDoctorOnboardingComplete::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            return match (true) {
                $request->is('admin*') => route('admin.login'),
                $request->is('medical-center*') => route('medical-center.login'),
                $request->is('doctor*') => route('doctor.login'),
                default => route('login'),
            };
        });

        $middleware->redirectUsersTo(function (Request $request) {
            return match (true) {
                $request->is('admin*') => route('admin.dashboard'),
                $request->is('medical-center*') => route('medical-center.dashboard'),
                $request->is('doctor*') => route('doctor.dashboard'),
                default => route('home'),
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
