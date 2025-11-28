<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = ['name', 'image_id', 'active', 'type', 'is_set_as_home', 'priority', 'special_category', 'categories', 'slug'];

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class, "category_id");
    }

    public function image()
    {
        return $this->belongsTo(Media::class, "image_id");
    }

    public function parks()
    {
        return $this->hasMany(ParkCategories::class, "category_id");
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
