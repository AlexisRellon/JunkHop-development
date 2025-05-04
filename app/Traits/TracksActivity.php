<?php

namespace App\Traits;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

trait TracksActivity
{
    /**
     * Boot the trait
     */
    public static function bootTracksActivity()
    {
        // Log model creation
        static::created(function (Model $model) {
            ActivityLogger::log(
                $model->getActivityType(),
                $model,
                'created'
            );
        });
        
        // Log model updates
        static::updated(function (Model $model) {
            ActivityLogger::log(
                $model->getActivityType(),
                $model,
                'updated'
            );
        });
        
        // Log model deletions
        static::deleted(function (Model $model) {
            ActivityLogger::log(
                $model->getActivityType(),
                $model,
                'deleted'
            );
        });
    }
    
    /**
     * Get the type of activity (user, junkshop, merchant, transaction)
     * 
     * @return string
     */
    public function getActivityType(): string
    {
        return defined('static::ACTIVITY_TYPE') ? static::ACTIVITY_TYPE : strtolower(class_basename($this));
    }
}
