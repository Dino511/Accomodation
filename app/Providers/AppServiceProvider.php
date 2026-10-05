<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
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
        // the email with the link to set a password (new account or forgotten password)
        ResetPassword::toMailUsing(function (object $user, string $token) {
            return (new MailMessage)
                ->subject('Set your Guest Accommodation password')
                ->greeting('Hello '.$user->name.',')
                ->line('Use the button below to set the password for your Guest Accommodation account.')
                ->action('Set my password', route('password.reset', ['token' => $token, 'email' => $user->email]))
                ->line('This link works for '.config('auth.passwords.users.expire').' minutes. If it has expired, use "Forgot password?" on the login page.')
                ->line('If you did not expect this email, you can ignore it.');
        });
    }
}
