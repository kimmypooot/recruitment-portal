<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification
{
    private readonly string $applicantName;
    private readonly string $positionTitle;
    private readonly string $newStatusLabel;

    public function __construct(
        private readonly Application $application,
        private readonly string $oldStatus,
        private readonly string $newStatus,
    ) {
        $profile       = $this->application->applicant;
        $this->applicantName = $profile ? trim("{$profile->first_name} {$profile->last_name}") : 'Applicant';
        $this->positionTitle = $this->application->vacancy?->position_title ?? 'the applied position';
        $this->newStatusLabel = $this->formatLabel($this->newStatus);
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $details = $this->statusDetails();

        return (new MailMessage)
            ->subject("Application Update — {$this->positionTitle}")
            ->greeting("Dear {$this->applicantName},")
            ->line("There has been an update on your application for **{$this->positionTitle}**.")
            ->line("**Status:** {$details['headline']}")
            ->when(
                $details['description'],
                fn ($m) => $m->line($details['description'])
            )
            ->action('View Your Application', url('/applicant/applications'))
            ->line('Thank you for your continued interest in the CSC RO VIII.')
            ->salutation('CSC RO VIII - Recruitment Portal');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'vacancy_title'  => $this->positionTitle,
            'old_status'     => $this->oldStatus,
            'new_status'     => $this->newStatus,
            'message'        => $this->statusDetails()['headline'],
        ];
    }

    private function formatLabel(string $status): string
    {
        return match ($status) {
            'submitted'      => 'Submitted',
            'under_review'   => 'Under Review',
            'screened'       => 'Screened',
            'qualified'      => 'Qualified',
            'disqualified'   => 'Not Qualified',
            'exam_scheduled' => 'Exam Scheduled',
            'shortlisted'    => 'Shortlisted',
            'for_interview'  => 'For Interview',
            'interviewed'    => 'Interviewed',
            'recommended'    => 'Recommended',
            'appointed'      => 'Appointed',
            'completed'      => 'Completed',
            'withdrawn'      => 'Withdrawn',
            'failed'         => 'Not Passed',
            default          => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    private function statusDetails(): array
    {
        return match ($this->newStatus) {
            'submitted' => [
                'headline' => 'Your application has been received.',
                'description' => 'We are currently reviewing your submission. You will receive another notification once it has been processed.',
            ],
            'under_review' => [
                'headline' => 'Your application is now under review.',
                'description' => 'Our team is evaluating your qualifications and documents. We will keep you posted on any updates.',
            ],
            'screened' => [
                'headline' => 'Your application has passed the initial screening.',
                'description' => 'We have reviewed your documents, and you meet the minimum qualifications for this position. We will notify you of the next steps.',
            ],
            'qualified' => [
                'headline' => 'You have been qualified for the position.',
                'description' => 'Congratulations! Based on your qualifications, you are eligible to proceed in the selection process. Please wait for further instructions.',
            ],
            'disqualified' => [
                'headline' => 'We regret to inform you that your application was not qualified.',
                'description' => 'After careful evaluation, we found that your qualifications do not fully meet the requirements for this position. We encourage you to apply for other positions that match your profile. Thank you for your interest in the CSC RO VIII.',
            ],
            'exam_scheduled' => [
                'headline' => 'You have been scheduled for an exam.',
                'description' => 'Please check your application for the date, time, and venue of your examination. Make sure to bring a valid government-issued ID.',
            ],
            'shortlisted' => [
                'headline' => 'You have been shortlisted for the position.',
                'description' => 'Great news! You have advanced to the next stage of the selection process. We will contact you with more details soon.',
            ],
            'for_interview' => [
                'headline' => 'You are invited for an interview.',
                'description' => 'Please check your application for the schedule and venue of your interview. Prepare any relevant documents you would like to present.',
            ],
            'interviewed' => [
                'headline' => 'You have completed your interview.',
                'description' => 'Thank you for attending the interview. Your application is now being evaluated along with other candidates. We will notify you of the results.',
            ],
            'recommended' => [
                'headline' => 'You have been recommended for appointment.',
                'description' => 'Congratulations! You are among the candidates recommended for the position. We will be in touch with the next steps regarding your appointment.',
            ],
            'appointed' => [
                'headline' => 'Congratulations! You have been appointed to the position.',
                'description' => 'We are pleased to inform you that you have been appointed. Please check your application for further instructions on the next steps, including onboarding requirements.',
            ],
            'completed' => [
                'headline' => 'The selection process has been completed.',
                'description' => 'Thank you for participating in the recruitment process. If you were appointed, welcome aboard! If not, we hope to see you apply for future opportunities.',
            ],
            'withdrawn' => [
                'headline' => 'Your application has been withdrawn.',
                'description' => 'Your application has been withdrawn as requested. If this was a mistake or you change your mind, please contact our office.',
            ],
            'failed' => [
                'headline' => 'We are sorry, but you did not pass this stage.',
                'description' => 'Thank you for your effort and participation. We encourage you to apply again for future vacancies that match your qualifications.',
            ],
            default => [
                'headline' => "Your application status has been updated to: {$this->newStatusLabel}.",
                'description' => null,
            ],
        };
    }
}
