<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Services\EmailTemplateMailBuilder;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification
{
    public static $createUrlCallback;

    public static $toMailCallback;

    public function __construct(
        #[\SensitiveParameter] public string $token,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return $this->buildMailMessage($this->resetUrl($notifiable));
    }

    protected function buildMailMessage(string $url): MailMessage
    {
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return EmailTemplateMailBuilder::build('password_reset', [
            '{{expiry_minutes}}' => (string) $expire,
        ], $url);
    }

    protected function resetUrl($notifiable): string
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
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
