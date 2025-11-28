<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $table = 'subcategories';

    protected $fillable = ['name', 'category_id', 'image_id', 'active', 'description', 'slug'];

    public function category()
    {
        return $this->belongsTo(Category::class, "category_id");
    }
    public function parkcategory()
    {
        return $this->hasMany(ParkCategories::class, 'subcategory_id');
    }

    public function image()
    {
        return $this->belongsTo(Media::class, "image_id");
    }

    public function parks()
    {
        return $this->hasManyThrough(
            Parks::class,
            ParkCategories::class,
            'subcategory_id',
            'id',
            'id',
            'park_id'
        );
    }

    public function scopeSearch($query, $term)
    {
        $normalizedTerm = $this->normalizeString($term);

        return $query->where(function ($query) use ($normalizedTerm) {
            $query->whereRaw('REPLACE(REPLACE(REPLACE(REPLACE(name, "-", ""), ".", ""), "&", "and"), " ", "") LIKE ?', ["%{$normalizedTerm}%"])
                ->orWhereRaw('REPLACE(REPLACE(REPLACE(REPLACE(name, "-", ""), ".", ""), "and", "&"), " ", "") LIKE ?', ["%{$normalizedTerm}%"]);
        });
    }

    protected function normalizeString($string)
    {
        return str_replace(['-', '.', '&', ' '], ['', '', 'and', ''], strtolower($string));
    }

    public function meta()
    {
        return $this->morphOne(Meta::class, 'metable');
    }
}
