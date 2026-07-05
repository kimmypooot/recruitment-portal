<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $statusSample = [
            'applicant_name' => 'Juan Dela Cruz',
            'position_title' => 'Administrative Officer II',
        ];

        $statuses = [
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
        ];

        $templates = [];

        foreach ($statuses as $status => $copy) {
            $templates["application_status_{$status}"] = [
                'name' => 'Status Update — '.ucwords(str_replace('_', ' ', $status)),
                'category' => 'Application Status',
                'subject' => 'Application Update — {{position_title}}',
                'greeting' => 'Dear {{applicant_name}},',
                'body' => "There has been an update on your application for **{{position_title}}**.\n"
                    ."**Status:** {$copy['headline']}\n"
                    ."{$copy['description']}\n"
                    .'Thank you for your continued interest in the CSC RO VIII.',
                'action_text' => 'View Your Application',
                'action_url' => '/applicant/applications',
                'action_locked' => false,
                'placeholders' => ['applicant_name', 'position_title'],
                'sample_data' => $statusSample,
            ];
        }

        $templates['application_status_default'] = [
            'name' => 'Status Update — Generic Fallback',
            'category' => 'Application Status',
            'subject' => 'Application Update — {{position_title}}',
            'greeting' => 'Dear {{applicant_name}},',
            'body' => "There has been an update on your application for **{{position_title}}**.\n"
                ."**Status:** Your application status has been updated to: {{status_label}}.\n"
                .'Thank you for your continued interest in the CSC RO VIII.',
            'action_text' => 'View Your Application',
            'action_url' => '/applicant/applications',
            'action_locked' => false,
            'placeholders' => ['applicant_name', 'position_title', 'status_label'],
            'sample_data' => $statusSample + ['status_label' => 'In Progress'],
        ];

        $templates['application_submitted'] = [
            'name' => 'Application Received',
            'category' => 'Application Lifecycle',
            'subject' => 'Application Received — {{position_title}}',
            'greeting' => 'Dear Applicant,',
            'body' => "Thank you for applying for the position of **{{position_title}}**.\n"
                ."Your application has been received and is now being reviewed. We will notify you once there is an update on your status.\n"
                .'If you have any questions, feel free to reach out to the CSC RO VIII.',
            'action_text' => 'View Your Application',
            'action_url' => '/applicant/applications',
            'action_locked' => false,
            'placeholders' => ['position_title'],
            'sample_data' => ['position_title' => 'Administrative Officer II'],
        ];

        $templates['exam_scheduled'] = [
            'name' => 'Exam Scheduled',
            'category' => 'Examination',
            'subject' => 'Exam Scheduled — {{position_title}}',
            'greeting' => 'Dear {{first_name}},',
            'body' => "Congratulations! You have qualified for the written examination for the position of **{{position_title}}**.\n"
                ."Here are the details of your exam:\n"
                ."- **Type:** {{exam_type}}\n"
                ."- **Date:** {{date}}\n"
                ."- **Time:** {{time}}\n"
                ."- **Venue:** {{venue}}\n"
                ."{{notes_block}}\n"
                ."Please arrive at least 15 minutes early. Bring a valid government-issued ID and your own ballpen.\n"
                .'For questions, please contact the HR Management and Practices Section.',
            'action_text' => 'View My Application',
            'action_url' => '/applicant/applications',
            'action_locked' => false,
            'placeholders' => ['first_name', 'position_title', 'exam_type', 'date', 'time', 'venue', 'notes_block'],
            'sample_data' => [
                'first_name' => 'Juan',
                'position_title' => 'Administrative Officer II',
                'exam_type' => 'Technical Written Examination',
                'date' => 'August 15, 2026',
                'time' => '9:00 AM',
                'venue' => 'CSC RO VIII Training Room',
                'notes_block' => '- **Reminders:** Bring your own calculator.',
            ],
        ];

        $templates['interview_scheduled'] = [
            'name' => 'Interview Scheduled',
            'category' => 'Interview',
            'subject' => 'Interview Scheduled — {{position_title}}',
            'greeting' => 'Dear {{first_name}},',
            'body' => "Congratulations on passing the written exam! You are invited to a **Behavioral Event Interview (BEI)** for the position of **{{position_title}}**.\n"
                ."Here are the details of your interview:\n"
                ."- **Date:** {{date}}\n"
                ."- **Time:** {{time}}\n"
                ."- **Venue:** {{venue}}\n"
                ."{{notes_block}}\n"
                ."Please arrive at least 15 minutes early. Bring a valid government-issued ID and any supporting documents you wish to present.\n"
                .'For questions, please contact the HR Management and Practices Section.',
            'action_text' => 'View My Application',
            'action_url' => '/applicant/applications',
            'action_locked' => false,
            'placeholders' => ['first_name', 'position_title', 'date', 'time', 'venue', 'notes_block'],
            'sample_data' => [
                'first_name' => 'Juan',
                'position_title' => 'Administrative Officer II',
                'date' => 'August 22, 2026',
                'time' => '1:30 PM',
                'venue' => 'CSC RO VIII Conference Room',
                'notes_block' => '- **Additional Info:** Business attire is required.',
            ],
        ];

        $templates['shortlist_result_shortlisted'] = [
            'name' => 'Shortlist Result — Shortlisted',
            'category' => 'Shortlisting',
            'subject' => 'Good News — You Have Been Shortlisted!',
            'greeting' => 'Dear {{name}},',
            'body' => "Congratulations! We are pleased to inform you that you have been shortlisted for the position of **{{position_title}}**.\n"
                ."You will receive further instructions regarding the next steps of the selection process. Please keep an eye on your notifications and email.\n"
                .'We look forward to meeting you in the next stage of the process.',
            'action_text' => 'View Your Application',
            'action_url' => '/applicant/applications',
            'action_locked' => false,
            'placeholders' => ['name', 'position_title'],
            'sample_data' => ['name' => 'Juan Dela Cruz', 'position_title' => 'Administrative Officer II'],
        ];

        $templates['shortlist_result_not_shortlisted'] = [
            'name' => 'Shortlist Result — Not Shortlisted',
            'category' => 'Shortlisting',
            'subject' => 'Update on Your Application — {{position_title}}',
            'greeting' => 'Dear {{name}},',
            'body' => "Thank you for your interest in the position of **{{position_title}}**.\n"
                ."After careful evaluation, we regret to inform you that you were not selected for the shortlist at this time.\n"
                .'We encourage you to apply again for future vacancies that match your qualifications. Thank you for your understanding.',
            'action_text' => 'Browse Other Vacancies',
            'action_url' => '/',
            'action_locked' => false,
            'placeholders' => ['name', 'position_title'],
            'sample_data' => ['name' => 'Juan Dela Cruz', 'position_title' => 'Administrative Officer II'],
        ];

        $templates['background_investigation_request'] = [
            'name' => 'Background Investigation Request',
            'category' => 'Background Investigation',
            'subject' => 'Background Investigation Request — {{position_title}}',
            'greeting' => 'Dear {{investigator_name}},',
            'body' => "You have been requested to conduct a background investigation for the following applicant:\n"
                ."**Applicant:** {{applicant_name}}\n"
                ."**Position Applied:** {{position_title}}\n"
                ."Please complete the background investigation report by uploading the signed PDF and providing your assessments. Your report must include:\n"
                ."- The completed and signed Background Investigation PDF\n"
                ."- Assessment on Competencies (minimum 1,000 characters)\n"
                ."- Assessment on Performance and Other Relevant Information (minimum 1,000 characters)\n"
                .'This link will expire on **{{expiry}}**. Please submit your report before the deadline.',
            'action_text' => 'Upload Background Investigation Report',
            'action_url' => null,
            'action_locked' => true,
            'placeholders' => ['investigator_name', 'applicant_name', 'position_title', 'expiry'],
            'sample_data' => [
                'investigator_name' => 'Maria Santos',
                'applicant_name' => 'Juan Dela Cruz',
                'position_title' => 'Administrative Officer II',
                'expiry' => 'August 30, 2026 at 5:00 PM',
            ],
        ];

        foreach ($templates as $key => $data) {
            EmailTemplate::firstOrCreate(['key' => $key], array_merge(['key' => $key], $data));
        }
    }
}
