<?php

namespace Controlink\LaravelWinmax4\app\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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

            static::restored(function ($model) {
                // Custom logic after restoring if necessary
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
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        $this->{$this->getDeletedAtColumn()} = null;
        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored', false);

        return $result;
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

    protected static function bootSoftDeletes()
    {
        static::addGlobalScope(new SoftDeletingScope);
    }
}