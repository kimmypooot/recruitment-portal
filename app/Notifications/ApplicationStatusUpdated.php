<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification
{
  public function __construct(
    private readonly Application $application,
    private readonly string $oldStatus,
    private readonly string $newStatus,
  ) {}

  // Which channels to send on (email + database bell icon)
  public function via(object $notifiable): array
  {
    return ['mail', 'database'];
  }

  // The email content
  public function toMail(object $notifiable): MailMessage
  {
    $profile       = $this->application->applicant;
    $applicantName = trim("{$profile->first_name} {$profile->last_name}");
    $positionTitle = $this->application->vacancy?->position_title ?? 'the applied position';
    $statusLabel   = ucfirst(str_replace('_', ' ', $this->newStatus));

    return (new MailMessage)
      ->subject("CSC Application Update: {$positionTitle}")
      ->greeting("Dear {$applicantName},")
      ->line("Your application for the position of {$positionTitle} has been updated.")
      ->line("New Status: {$statusLabel}")
      ->action('View Your Application', url("/my-applications/{$this->application->id}"))
      ->line('For questions, please contact the CSC Regional Office VIII.')
      ->salutation('Civil Service Commission Regional Office VIII');
  }

  // What is stored in the notifications database table (for the bell icon)
  public function toDatabase(object $notifiable): array
  {
    return [
      'application_id' => $this->application->id,
      'vacancy_title'  => $this->application->vacancy?->position_title ?? '—',
      'old_status'     => $this->oldStatus,
      'new_status'     => $this->newStatus,
      'message'        => 'Your application status has been updated to: ' . $this->newStatus,
    ];
  }
}
