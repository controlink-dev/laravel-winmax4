<?php

namespace Controlink\LaravelWinmax4\app\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait ConditionalSoftDeletes
{
    public static function bootConditionalSoftDeletes()
    {
        if (config('winmax4.use_soft_deletes')) {
            static::deleting(function ($model) {
                if (! $model->forceDeleting) {
                    $model->runSoftDelete();
                    return false;
                }
            });

            static::restoring(function ($model) {
                $model->{$model->getDeletedAtColumn()} = null;
                $model->exists = true;
                $model->save();
            });
        }
    }

    protected function runSoftDelete()
    {
        $this->{$this->getDeletedAtColumn()} = $time = $this->freshTimestamp();

        $this->newQueryWithoutScopes()->where($this->getKeyName(), $this->getKey())->update([
            $this->getDeletedAtColumn() => $this->fromDateTime($time),
        ]);

        $this->syncOriginal();
    }

    public function restore()
    {
        $this->{$this->getDeletedAtColumn()} = null;
        $this->exists = true;

        return $this->save();
    }

    public function trashed()
    {
        return ! is_null($this->{$this->getDeletedAtColumn()});
    }

    public function forceDelete()
    {
        $this->forceDeleting = true;

        $result = $this->delete();

        $this->forceDeleting = false;

        return $result;
    }
}
