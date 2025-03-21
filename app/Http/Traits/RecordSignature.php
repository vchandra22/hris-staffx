<?php

namespace App\Http\Traits;

trait RecordSignature
{
    protected static function bootRecordSignature()
    {
        static::saving(function ($model) {
            $model->updated_by = auth()->id() ?? "";
        });

        static::creating(function ($model) {
            $model->created_by = auth()->id() ?? "";
        });

        static::deleting(function ($model) {
            $model->deleted_by = auth()->id() ?? "";
        });
    }
}