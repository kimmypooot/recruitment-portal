<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use App\Services\EmailTemplateMailBuilder;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Notification
{
    public static $createUrlCallback;

    public static $toMailCallback;

    public function __construct(protected ?string $code = null)
    {
    }

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
        return EmailTemplateMailBuilder::build('email_verification', [
            '{{code}}' => $this->code ?? '——————',
            '{{expiry_minutes}}' => (string) User::VERIFICATION_CODE_TTL_MINUTES,
        ], $url);
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
