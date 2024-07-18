<?php

namespace Controlink\LaravelWinmax4\app\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait ConditionalSoftDeletes
{
    public static function bootConditionalSoftDeletes()
    {
        if (config('winmax4.use_soft_deletes')) {
            // Adiciona o trait SoftDeletes se ainda não estiver sendo usado
            static::addTraitIfNotExists(SoftDeletes::class);

            // Define o evento deleting para personalizar o comportamento de soft delete
            static::deleting(function ($model) {
                if (! $model->forceDeleting) {
                    $model->runSoftDelete();
                    return false;
                }
            });

            // Define o evento restored para restaurar corretamente o modelo após a restauração
            static::restored(function ($model) {
                // Implemente qualquer lógica adicional necessária após a restauração
            });
        }
    }

    protected static function addTraitIfNotExists($trait)
    {
        $usedTraits = class_uses_recursive(static::class);

        if (!in_array($trait, $usedTraits)) {
            $usedTraits[] = $trait;

            $reflection = new \ReflectionClass(static::class);
            $modelPath = $reflection->getFileName();
            $modelContent = file_get_contents($modelPath);

            $traitShortName = (new \ReflectionClass($trait))->getShortName();
            if (strpos($modelContent, "use $trait;") === false && strpos($modelContent, "use $traitShortName;") === false) {
                $modelContent = str_replace("use Illuminate\\Database\\Eloquent\\Model;", "use Illuminate\\Database\\Eloquent\\Model;\nuse $trait;", $modelContent);
                file_put_contents($modelPath, $modelContent);
            }
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
}
