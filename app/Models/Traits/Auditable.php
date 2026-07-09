<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the Auditable trait for a model.
     *
     * @return void
     */
    public static function bootAuditable()
    {
        static::creating(function ($model) {
            if (Auth::check() && Auth::id()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check() && Auth::id()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
