<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

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
        app()->setLocale('es');
        Carbon::setLocale('es');

        // Personalizar el correo de recuperación de contraseña al español
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            return (new MailMessage)
                ->subject('Restablecimiento de contraseña - ' . config('app.name'))
                ->greeting('¡Hola!')
                ->line('Estás recibiendo este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña para tu cuenta.')
                ->action('Restablecer contraseña', url(route('password.reset', [
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false)))
                ->line('Este enlace de restablecimiento de contraseña expirará en ' . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . ' minutos.')
                ->line('Si no solicitaste un restablecimiento de contraseña, no es necesario realizar ninguna otra acción.')
                ->salutation('Saludos, ' . config('app.name'));
        });
    }
}
