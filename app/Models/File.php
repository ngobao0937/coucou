<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }
}
