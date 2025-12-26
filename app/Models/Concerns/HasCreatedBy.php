<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasCreatedBy
{
    protected static function bootHasCreatedBy(): void
    {
        static::creating(function (Model $model): void {
            if ($model->isDirty('created_by')) {
                return;
            }

            $userId = auth()->id();
            if ($userId) {
                $model->setAttribute('created_by', $userId);
            }
        });
    }
}
