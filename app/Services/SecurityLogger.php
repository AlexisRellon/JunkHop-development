<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SecurityLogger extends ActivityLogger
{
    /**
     * Log a failed authentication attempt
     */
    public static function logFailedAuth(string $identifier, string $reason): Activity
    {
        return self::log(
            'security',
            null,
            'failed_auth',
            "Failed authentication attempt for {$identifier}: {$reason}",
            null,
            ['ip' => Request::ip(), 'user_agent' => Request::userAgent()]
        );
    }

    /**
     * Log a successful authentication
     */
    public static function logSuccessfulAuth(string $identifier): Activity
    {
        return self::log(
            'security',
            null,
            'successful_auth',
            "Successful authentication for {$identifier}",
            null,
            ['ip' => Request::ip(), 'user_agent' => Request::userAgent()]
        );
    }

    /**
     * Log a password reset request
     */
    public static function logPasswordReset(string $identifier): Activity
    {
        return self::log(
            'security',
            null,
            'password_reset',
            "Password reset requested for {$identifier}",
            null,
            ['ip' => Request::ip()]
        );
    }

    /**
     * Log an API token operation
     */
    public static function logTokenOperation(string $operation, string $tokenId): Activity
    {
        return self::log(
            'security',
            null,
            'token_' . $operation,
            "API token {$operation} - ID: {$tokenId}",
            Auth::id()
        );
    }

    /**
     * Log a suspicious activity
     */
    public static function logSuspiciousActivity(string $activity, array $context = []): Activity
    {
        return self::log(
            'security',
            null,
            'suspicious',
            "Suspicious activity detected: {$activity}",
            null,
            array_merge(['ip' => Request::ip(), 'user_agent' => Request::userAgent()], $context)
        );
    }

    /**
     * Log a permission change
     */
    public static function logPermissionChange(string $userId, string $permission, string $action): Activity
    {
        return self::log(
            'security',
            null,
            'permission_change',
            "Permission {$action} for user {$userId}: {$permission}",
            Auth::id()
        );
    }
}
