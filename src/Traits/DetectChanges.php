<?php

namespace JeremyNikolic\Revision\Traits;

use Illuminate\Database\Eloquent\Model;

trait DetectChanges
{

    protected array        $oldAttributeForRevision = [];

    public static function bootDetectChanges()
    {
        static::updating(function (Model $model) {
            $oldModelReplica = (new static())->setRawAttributes($model->getRawOriginal());

            $model->oldAttributeForRevision = static::detectChanges($oldModelReplica);
        });
    }

    public static function detectChanges(Model $model)
    {
        $changes = [];
        $attributes = $model->attributesToDetect();

        foreach ($attributes as $attribute) {
            $changes[$attribute] = $model->$attribute;
        }

        return $changes;
    }

    public function attributesToStoreInRevision(string $forEvent): array
    {
        if ( ! count($this->attributesToDetect())) {
            return [];
        }

        $properties['attributes'] = static::detectChanges($this);

        if ($forEvent == 'updated') {
            $areNullNow = array_fill_keys(array_keys($properties['attributes']), null);

            $properties['old'] = array_merge($areNullNow, $this->oldAttributeForRevision);
            $this->oldAttributeForRevision = [];
        }

        if ($this->onlyDirty() && isset($properties['old'])) {
            $properties['attributes'] = $this->getDirty();

            $properties['old'] = collect($properties['old'])
                ->only(array_keys($properties['attributes']))
                ->all();
        }

        return $properties;
    }

    public function onlyDirty(): bool
    {
        return isset(static::$detectOnlyDirty) ? static::$detectOnlyDirty : false;
    }

    public function attributesToDetect(): array
    {
        return $this->attributesToDetect;
    }

    public function compare($new, $old)
    {
        if ($old === null || $new === null) {
            return $new === $old ? 0 : 1;
        }

        return $new <=> $old;
    }
}
