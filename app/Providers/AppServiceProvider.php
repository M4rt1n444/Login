<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // (aquí lo que ya tuvieras en boot(), si había algo)

        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                ->subject('Restablece tu contraseña')
                ->greeting('Hola, '.$notifiable->name)
                ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
                ->action('Restablecer contraseña', $url)
                ->line('Este enlace expira en 30 minutos.')
                ->line('Si no hiciste esta solicitud, ignora este correo.');
        });
    }
}