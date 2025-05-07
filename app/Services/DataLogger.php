<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class DataLogger extends ActivityLogger
{
    /**
     * Log a database backup
     */
    public static function logBackup(string $filename, int $size): Activity
    {
        return self::log(
            'data',
            null,
            'backup_created',
            "Database backup created: {$filename} ({$size} bytes)",
            Auth::id(),
            ['filename' => $filename, 'size' => $size]
        );
    }

    /**
     * Log a bulk data operation
     */
    public static function logBulkOperation(string $operation, string $model, int $count): Activity
    {
        return self::log(
            'data',
            null,
            'bulk_operation',
            "Bulk {$operation} performed on {$model}: {$count} records affected",
            Auth::id(),
            ['operation' => $operation, 'model' => $model, 'count' => $count]
        );
    }

    /**
     * Log a data import
     */
    public static function logImport(string $type, int $total, int $success, int $failed): Activity
    {
        return self::log(
            'data',
            null,
            'data_import',
            "Data import completed for {$type}: {$success} successful, {$failed} failed",
            Auth::id(),
            ['type' => $type, 'total' => $total, 'success' => $success, 'failed' => $failed]
        );
    }

    /**
     * Log a data export
     */
    public static function logExport(string $type, int $count, string $format): Activity
    {
        return self::log(
            'data',
            null,
            'data_export',
            "Data export completed for {$type}: {$count} records in {$format} format",
            Auth::id(),
            ['type' => $type, 'count' => $count, 'format' => $format]
        );
    }

    /**
     * Log a data cleanup operation
     */
    public static function logCleanup(string $type, int $count): Activity
    {
        return self::log(
            'data',
            null,
            'data_cleanup',
            "Data cleanup completed for {$type}: {$count} records affected",
            Auth::id(),
            ['type' => $type, 'count' => $count]
        );
    }
}
