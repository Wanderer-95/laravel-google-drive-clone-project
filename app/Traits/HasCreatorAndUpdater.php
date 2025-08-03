<?php

namespace App\Traits;

use Auth;

trait HasCreatorAndUpdater
{
    protected static function bootHasCreatorAndUpdater(): void
    {
        static::creating(static function ($model) {
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        });

        static::updating(static function ($model) {
            $model->updated_by = Auth::id();
        });
    }
}
