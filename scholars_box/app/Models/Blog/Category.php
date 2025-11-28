<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug',
        'name',
    ];
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_category');
    }
}
