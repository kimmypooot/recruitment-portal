<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;

class EmailTemplateMailBuilder
{
    /**
     * Render a template's fields with the given placeholder replacements.
     * Replacement keys should already be wrapped, e.g. ['{{applicant_name}}' => 'Juan Dela Cruz'].
     */
    public static function render(EmailTemplate $template, array $replacements, ?string $forcedActionUrl = null): array
    {
        $actionUrl = $template->action_locked
            ? $forcedActionUrl
            : ($forcedActionUrl ?? $template->action_url);

        return [
            'subject' => strtr($template->subject, $replacements),
            'greeting' => strtr($template->greeting, $replacements),
            'body' => strtr($template->body, $replacements),
            'action_text' => $template->action_text ? strtr($template->action_text, $replacements) : null,
            'action_url' => $actionUrl,
            'footer' => $template->footer,
        ];
    }

    /**
     * Build a MailMessage from a template key, falling back to a minimal
     * generic message if the template is missing or has been deactivated.
     */
    public static function build(string $key, array $replacements, ?string $forcedActionUrl = null): MailMessage
    {
        $template = EmailTemplate::where('key', $key)->where('is_active', true)->first();

        if (! $template) {
            $mail = (new MailMessage)
                ->subject('Update on Your Application')
                ->greeting('Hello,')
                ->line('There has been an update regarding your application with CSC RO VIII.');

            if ($forcedActionUrl) {
                $mail->action('View Details', $forcedActionUrl);
            }

            return $mail->salutation('CSC RO VIII - Recruitment Portal');
        }

        $rendered = self::render($template, $replacements, $forcedActionUrl);

        $mail = (new MailMessage)
            ->subject($rendered['subject'])
            ->greeting($rendered['greeting']);

        foreach (preg_split('/\r?\n/', trim($rendered['body'])) as $line) {
            if ($line !== '') {
                $mail->line($line);
            }
        }

        if ($rendered['action_text'] && $rendered['action_url']) {
            $url = str_starts_with($rendered['action_url'], 'http')
                ? $rendered['action_url']
                : url($rendered['action_url']);

            $mail->action($rendered['action_text'], $url);
        }

        return $mail->salutation($rendered['footer']);
    }
}
