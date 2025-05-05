<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class TransactionLogger extends ActivityLogger
{
    /**
     * Log a bid creation
     */
    public static function logBidCreated(Model $bid, float $amount): Activity
    {
        return self::log(
            'transaction',
            $bid,
            'bid_created',
            "New bid created: Amount {$amount}",
            Auth::id(),
            ['amount' => $amount]
        );
    }

    /**
     * Log a bid status change
     */
    public static function logBidStatusChange(Model $bid, string $oldStatus, string $newStatus): Activity
    {
        return self::log(
            'transaction',
            $bid,
            'bid_status_changed',
            "Bid status changed from {$oldStatus} to {$newStatus}",
            Auth::id(),
            ['old_status' => $oldStatus, 'new_status' => $newStatus]
        );
    }

    /**
     * Log a payment
     */
    public static function logPayment(Model $payment, string $status, float $amount): Activity
    {
        return self::log(
            'transaction',
            $payment,
            'payment_' . $status,
            "Payment {$status}: Amount {$amount}",
            Auth::id(),
            ['amount' => $amount, 'status' => $status]
        );
    }

    /**
     * Log a quality verification
     */
    public static function logQualityVerification(Model $verification, string $result): Activity
    {
        return self::log(
            'transaction',
            $verification,
            'quality_verification',
            "Quality verification completed: {$result}",
            Auth::id(),
            ['result' => $result]
        );
    }

    /**
     * Log a dispute
     */
    public static function logDispute(Model $dispute, string $reason): Activity
    {
        return self::log(
            'transaction',
            $dispute,
            'dispute_created',
            "Dispute created: {$reason}",
            Auth::id(),
            ['reason' => $reason]
        );
    }
}
