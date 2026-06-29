<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Notification
{
    public static $createUrlCallback;

    public static $toMailCallback;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        return $this->buildMailMessage($verificationUrl, $notifiable);
    }

    protected function buildMailMessage(string $url, $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify Your Email — CSC RO VIII')
            ->greeting('Welcome to CSC RO VIII!')
            ->line('Thank you for creating an account with the Civil Service Commission Regional Office VIII - Recruitment Portal.')
            ->line('Please click the button below to verify your email address and activate your account.')
            ->action('Verify Email Address', $url)
            ->line('If you did not create an account, no further action is required.');
    }

    protected function verificationUrl($notifiable): string
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    public static function createUrlUsing(callable $callback): void
    {
        static::$createUrlCallback = $callback;
    }

    public static function toMailUsing(callable $callback): void
    {
        static::$toMailCallback = $callback;
    }
}
