<?php

namespace App\Services;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/**
 * Single entry point for sending Email and SMS notifications.
 *
 * Every send attempt (success or failure) is logged to notification_logs,
 * so admins can audit what was sent and to whom, and troubleshoot delivery
 * problems without digging through mail/SMS provider dashboards.
 *
 * Email uses Laravel's built-in Mail facade — configure MAIL_* in .env.
 * SMS uses a generic HTTP gateway — configure SMS_GATEWAY_URL, SMS_API_KEY,
 * SMS_SENDER_ID in .env (see config/services.php).
 *
 * If no mail/SMS credentials are configured, calls fail gracefully and are
 * logged as 'failed' rather than throwing — a missing notification should
 * never break the primary action (e.g. publishing a notice).
 */
class NotificationService
{
    public function sendEmail(string $to, string $subject, string $message, $related = null, ?int $triggeredBy = null): NotificationLog
    {
        $status = 'sent';
        $error = null;

        try {
            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)->subject($subject);
            });
        } catch (\Throwable $e) {
            $status = 'failed';
            $error = $e->getMessage();
            report($e);
        }

        return $this->log('email', $to, $subject, $message, $status, $error, $related, $triggeredBy);
    }

    public function sendSms(string $to, string $message, $related = null, ?int $triggeredBy = null): NotificationLog
    {
        $status = 'sent';
        $error = null;

        try {
            $url = config('services.sms.url');

            if (! $url) {
                throw new \RuntimeException('SMS gateway URL not configured (SMS_GATEWAY_URL in .env).');
            }

            $response = Http::timeout(10)->post($url, [
                'api_key' => config('services.sms.api_key'),
                'sender_id' => config('services.sms.sender_id'),
                'number' => $to,
                'message' => $message,
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException('SMS gateway returned an error: ' . $response->status());
            }
        } catch (\Throwable $e) {
            $status = 'failed';
            $error = $e->getMessage();
            report($e);
        }

        return $this->log('sms', $to, null, $message, $status, $error, $related, $triggeredBy);
    }

    // Sends via whichever channels are given a non-empty recipient — used
    // when notifying a student who might only have email or only phone on file.
    public function notifyStudent(\App\Models\Student $student, string $subject, string $message, $related = null, ?int $triggeredBy = null): void
    {
        if ($student->email) {
            $this->sendEmail($student->email, $subject, $message, $related, $triggeredBy);
        }

        if ($student->phone) {
            $this->sendSms($student->phone, $message, $related, $triggeredBy);
        }
    }

    private function log(string $channel, string $recipient, ?string $subject, string $message, string $status, ?string $error, $related, ?int $triggeredBy): NotificationLog
    {
        return NotificationLog::create([
            'channel' => $channel,
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message,
            'status' => $status,
            'error' => $error,
            'related_type' => $related ? get_class($related) : null,
            'related_id' => $related?->id,
            'triggered_by' => $triggeredBy,
        ]);
    }
}