<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Mail;
use App\Mail\Transports\MailtrapTransport;

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
        Mail::extend('mailtrap', function (array $config = []) {
            return new MailtrapTransport(
                $config['api_token'] ?? env('MAILTRAP_API_TOKEN', 'ef8d48c65ad8854d31d1a9885befb36e'),
                (string) ($config['inbox_id'] ?? env('MAILTRAP_INBOX_ID', '4932332'))
            );
        });
    }
}
