<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class PerformanceLogger extends ActivityLogger
{
    /**
     * Log a slow query
     */
    public static function logSlowQuery(string $sql, float $duration, array $bindings = []): Activity
    {
        return self::log(
            'performance',
            null,
            'slow_query',
            "Slow query detected: {$duration}ms",
            null,
            [
                'sql' => $sql,
                'duration' => $duration,
                'bindings' => $bindings
            ]
        );
    }

    /**
     * Log a memory usage spike
     */
    public static function logMemorySpike(float $memoryUsage, string $endpoint): Activity
    {
        return self::log(
            'performance',
            null,
            'memory_spike',
            "High memory usage detected: {$memoryUsage}MB on {$endpoint}",
            null,
            ['memory_usage' => $memoryUsage, 'endpoint' => $endpoint]
        );
    }

    /**
     * Log API response times
     */
    public static function logApiPerformance(string $endpoint, float $duration, int $statusCode): Activity
    {
        return self::log(
            'performance',
            null,
            'api_performance',
            "API response time: {$duration}ms for {$endpoint}",
            null,
            [
                'endpoint' => $endpoint,
                'duration' => $duration,
                'status_code' => $statusCode
            ]
        );
    }

    /**
     * Log cache performance
     */
    public static function logCachePerformance(string $key, string $operation, float $duration): Activity
    {
        return self::log(
            'performance',
            null,
            'cache_performance',
            "Cache {$operation}: {$duration}ms for key {$key}",
            null,
            [
                'key' => $key,
                'operation' => $operation,
                'duration' => $duration
            ]
        );
    }

    /**
     * Log job execution performance
     */
    public static function logJobPerformance(string $job, float $duration, string $status): Activity
    {
        return self::log(
            'performance',
            null,
            'job_performance',
            "Job execution: {$job} completed in {$duration}ms",
            null,
            [
                'job' => $job,
                'duration' => $duration,
                'status' => $status
            ]
        );
    }
}
