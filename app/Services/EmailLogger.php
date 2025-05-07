<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class EmailLogger extends ActivityLogger
{
    /**
     * Log an email being sent
     */
    public static function logEmailSent(string $type, string $recipient, string $subject): Activity
    {
        return self::log(
            'email',
            null,
            'email_sent',
            "Email sent ({$type}): {$subject}",
            Auth::id(),
            ['type' => $type, 'recipient' => $recipient, 'subject' => $subject]
        );
    }

    /**
     * Log an email failure
     */
    public static function logEmailFailure(string $type, string $recipient, string $error): Activity
    {
        return self::log(
            'email',
            null,
            'email_failed',
            "Email failed ({$type}): {$error}",
            Auth::id(),
            ['type' => $type, 'recipient' => $recipient, 'error' => $error]
        );
    }

    /**
     * Log an email bounce
     */
    public static function logEmailBounce(string $recipient, string $reason): Activity
    {
        return self::log(
            'email',
            null,
            'email_bounced',
            "Email bounced for {$recipient}: {$reason}",
            null,
            ['recipient' => $recipient, 'reason' => $reason]
        );
    }

    /**
     * Log a bulk email operation
     */
    public static function logBulkEmail(string $type, int $total, int $sent, int $failed): Activity
    {
        return self::log(
            'email',
            null,
            'bulk_email',
            "Bulk email ({$type}): {$sent} sent, {$failed} failed",
            Auth::id(),
            ['type' => $type, 'total' => $total, 'sent' => $sent, 'failed' => $failed]
        );
    }
}
