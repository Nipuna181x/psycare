<?php

namespace App\Providers;

use App\Services\StripeCheckoutGateway;
use App\Services\StripeHttpCheckoutGateway;
use App\View\Composers\DoctorPortalComposer;
use App\View\Composers\MedicalCenterPortalComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StripeCheckoutGateway::class, StripeHttpCheckoutGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.doctor', DoctorPortalComposer::class);
        View::composer('layouts.medical-center', MedicalCenterPortalComposer::class);
    }
}
