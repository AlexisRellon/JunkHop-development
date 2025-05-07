<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log an activity
     *
     * @param string $type Type of activity (user, junkshop, merchant, transaction)
     * @param Model|null $subject The subject model of the activity
     * @param string $action Action performed (created, updated, deleted)
     * @param string|null $description Custom description (will be auto-generated if null)
     * @param string|null $userId User ID (will use authenticated user if null)
     * @param array $changes Array of changes with old and new values
     * @return Activity
     */    
    public static function log(
        string $type,
        ?Model $subject = null,
        string $action = 'created',
        ?string $description = null,
        ?string $userId = null,
        array $changes = []
    ): Activity {
        $userId = $userId ?? (Auth::check() ? Auth::user()->ulid : null);
        
        // Auto-generate description if not provided
        if (!$description && $subject) {
            $modelName = class_basename($subject);
            $subjectName = '';
            
            // Try to get a meaningful name from the subject
            if (isset($subject->name)) {
                $subjectName = $subject->name;
            } elseif (isset($subject->title)) {
                $subjectName = $subject->title;
            } elseif (isset($subject->business_name)) {
                $subjectName = $subject->business_name;
            } elseif (isset($subject->email)) {
                $subjectName = $subject->email;
            }
            
            // Format description based on action and changes
            switch ($action) {
                case 'created':
                    $description = "New $modelName created: $subjectName";
                    break;
                case 'updated':
                    if (!empty($changes)) {
                        $changeDesc = [];
                        foreach ($changes as $field => $values) {
                            // Format the change description based on the value type
                            if (is_bool($values['old']) || is_bool($values['new'])) {
                                // Handle boolean values
                                $changeDesc[] = "$field changed from " . 
                                    ($values['old'] ? 'true' : 'false') . " to " . 
                                    ($values['new'] ? 'true' : 'false');
                            } elseif (is_numeric($values['old']) && is_numeric($values['new'])) {
                                // Handle numeric values (add + sign for increases)
                                $diff = $values['new'] - $values['old'];
                                $sign = $diff > 0 ? '+' : '';
                                $changeDesc[] = "$field changed from {$values['old']} to {$values['new']} ($sign$diff)";
                            } else {
                                // Handle string and other values
                                $changeDesc[] = "$field changed from '{$values['old']}' to '{$values['new']}'";
                            }
                        }
                        $description = "$modelName ($subjectName) updated: " . implode(', ', $changeDesc);
                    } else {
                        $description = "$modelName updated: $subjectName";
                    }
                    break;
                case 'deleted':
                    $description = "$modelName deleted: $subjectName";
                    break;
                default:
                    $description = "$action $modelName: $subjectName";
            }
        }
        
        // Create activity record
        return Activity::create([
            'user_id' => $userId,
            'type' => $type,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'action' => $action,
            'description' => $description ?? 'Activity performed',
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
    
    /**
     * Log a user activity
     */
    public static function logUser(Model $user, string $action, ?string $description = null): Activity
    {
        return self::log('user', $user, $action, $description);
    }
    
    /**
     * Log a junkshop activity
     */
    public static function logJunkshop(Model $junkshop, string $action, ?string $description = null): Activity
    {
        return self::log('junkshop', $junkshop, $action, $description);
    }
    
    /**
     * Log a merchant activity
     */
    public static function logMerchant(Model $merchant, string $action, ?string $description = null): Activity
    {
        return self::log('merchant', $merchant, $action, $description);
    }
    
    /**
     * Log a transaction activity
     */
    public static function logTransaction(Model $transaction, string $action, ?string $description = null): Activity
    {
        return self::log('transaction', $transaction, $action, $description);
    }
}
