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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $settings = \App\Models\Setting::pluck('value', 'key')->toArray();

                view()->share([
                    'app_name' => $settings['school_name'] ?? 'Sekolah CBT',
                    'app_logo' => $settings['school_logo'] ?? null,
                    'app_academic_year' => $settings['academic_year'] ?? date('Y') . '/' . (date('Y') + 1),
                    'app_semester' => $settings['semester'] ?? 'Ganjil',
                ]);
            }
        } catch (\Exception $e) {
            // Log or ignore during migration/install
        }
    }
}
