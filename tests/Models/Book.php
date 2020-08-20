<?php

namespace JeremyNikolic\Revision\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use JeremyNikolic\Revision\Traits\DetectChanges;

class Book extends Model
{

    use DetectChanges;

    protected     $table              = 'books';

    protected     $guarded            = [];

    public static $attributesToDetect = ['title'];

    public static $detectOnlyDirty    = false;

    protected     $casts              = [
        'updated_at' => 'datetime:Y-m-d',
        'content'    => 'array',
    ];
}
