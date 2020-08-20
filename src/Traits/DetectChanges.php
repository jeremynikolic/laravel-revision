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
            $changes[$attribute] = $model->getAttribute($attribute);
            if (is_null($changes[$attribute])) {
                continue;
            }

            if ($model->isDateAttribute($attribute)) {
                $changes[$attribute] = $model->serializeDate($model->asDateTime($changes[$attribute]));
            }

            if ($model->hasCast($attribute)) {
                $cast = $model->getCasts()[$attribute];

                if ($model->isCustomDateTimeCast($cast)) {
                    $changes[$attribute] = $model->asDateTime($changes[$attribute])
                                                 ->format(explode(':', $cast, 2)[1]);
                }
            }
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
            $properties['attributes'] = array_udiff($properties['attributes'], $properties['old'], [$this, 'compare']);

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
        return static::$attributesToDetect;
    }

    public function compare($new, $old)
    {
        if ($old === null || $new === null) {
            return $new === $old ? 0 : 1;
        }

        return $new <=> $old;
    }
}
