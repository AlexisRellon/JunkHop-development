<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class AdminLogger extends ActivityLogger
{
    /**
     * Log system configuration changes
     */
    public static function logConfigChange(string $config, string $oldValue, string $newValue): Activity
    {
        return self::log(
            'admin',
            null,
            'config_change',
            "System configuration changed: {$config}",
            Auth::id(),
            [
                'config' => $config,
                'old_value' => $oldValue,
                'new_value' => $newValue
            ]
        );
    }

    /**
     * Log content moderation actions
     */
    public static function logModeration(Model $content, string $action, string $reason): Activity
    {
        return self::log(
            'admin',
            $content,
            'moderation',
            "Content moderation: {$action} - {$reason}",
            Auth::id(),
            ['action' => $action, 'reason' => $reason]
        );
    }

    /**
     * Log verification actions
     */
    public static function logVerification(Model $subject, string $action, string $notes = null): Activity
    {
        return self::log(
            'admin',
            $subject,
            'verification',
            "Verification {$action}" . ($notes ? ": {$notes}" : ''),
            Auth::id(),
            ['action' => $action, 'notes' => $notes]
        );
    }

    /**
     * Log system maintenance
     */
    public static function logMaintenance(string $action, string $details): Activity
    {
        return self::log(
            'admin',
            null,
            'maintenance',
            "System maintenance: {$action} - {$details}",
            Auth::id(),
            ['action' => $action, 'details' => $details]
        );
    }

    /**
     * Log bulk administrative actions
     */
    public static function logBulkAction(string $action, string $type, int $count, array $details = []): Activity
    {
        return self::log(
            'admin',
            null,
            'bulk_action',
            "Bulk {$action} on {$type}: {$count} items affected",
            Auth::id(),
            array_merge(['action' => $action, 'type' => $type, 'count' => $count], $details)
        );
    }

    /**
     * Log rate limit changes
     */
    public static function logRateLimitChange(string $resource, int $oldLimit, int $newLimit): Activity
    {
        return self::log(
            'admin',
            null,
            'rate_limit_change',
            "Rate limit changed for {$resource}: {$oldLimit} to {$newLimit}",
            Auth::id(),
            [
                'resource' => $resource,
                'old_limit' => $oldLimit,
                'new_limit' => $newLimit
            ]
        );
    }
}
