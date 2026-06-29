<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated');
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    protected function logActivity($action)
    {
        $causer = Auth::user();
        
        $properties = [];
        if ($action === 'updated') {
            $properties = [
                'old' => $this->getOriginal(),
                'attributes' => $this->getChanges(),
            ];
        } else {
            $properties = [
                'attributes' => $this->getAttributes(),
            ];
        }

        $logName = strtolower(class_basename($this));

        ActivityLog::create([
            'log_name' => $logName,
            'description' => "{$logName} {$action}",
            'subject_type' => get_class($this),
            'subject_id' => $this->getKey(),
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer ? $causer->id : null,
            'properties' => $properties,
        ]);
    }
}
