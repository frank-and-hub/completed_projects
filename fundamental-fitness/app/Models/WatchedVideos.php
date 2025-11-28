<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WatchedVideos extends Model
{
    use SoftDeletes;
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.watched_videos');
    }

    protected $fillable = [
        'exercise_id',
        'user_id',
        'video_count',
    ];
}
