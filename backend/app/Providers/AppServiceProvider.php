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
        $base64 = env('FIREBASE_SERVICE_ACCOUNT_BASE64');

        if ($base64) {
            $jsonPath = storage_path('app/firebase_service_account.json');
            file_put_contents($jsonPath, base64_decode($base64));

            // Make Firebase SDK find it
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $jsonPath);
        }
    }
}
