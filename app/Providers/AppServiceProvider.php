<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\TenantContext::class, function () {
            return new \App\Services\TenantContext();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // إرفاق tenant_id تلقائياً مع كل عملية تسجيل نشاط في activity_log
        if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
            \Spatie\Activitylog\Models\Activity::saving(function ($activity) {
                if (empty($activity->tenant_id)) {
                    $activity->tenant_id = app(\App\Services\TenantContext::class)->id();
                }
            });
        }
    }
}
