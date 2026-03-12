<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait CustomerTrait
{
    public static function bootBlameable(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $userId = Auth::id();
                if (!$model->isDirty('created_by')) {
                    $model->created_by = $userId;
                }
                if (!$model->isDirty('updated_by')) {
                    $model->updated_by = $userId;
                }
                Log::info('Создание записи', ['model' => get_class($model), 'user_id' => $userId]);
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $userId = Auth::id();
                if (!$model->isDirty('updated_by')) {
                    $model->updated_by = $userId;
                }
                Log::info('Обновление записи', ['model' => get_class($model), 'user_id' => $userId]);
            }
        });

        static::deleting(function ($model) {
            if (Auth::check() && !$model->isForceDeleting()) {
                $model->deleted_by = Auth::id();
                $model->saveQuietly();
                Log::info('Мягкое удаление записи', ['model' => get_class($model), 'user_id' => Auth::id()]);
            }
        });

        static::restoring(function ($model) {
            if (Auth::check()) {
                $model->deleted_by = null;
                Log::info('Восстановление записи', ['model' => get_class($model), 'user_id' => Auth::id()]);
            }
        });

        static::forceDeleting(function ($model) {
            if (Auth::check()) {
                Log::info('Жёсткое удаление записи', ['model' => get_class($model), 'user_id' => Auth::id()]);
            }
        });
    }
}