<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkCategories extends Model
{
    protected $table = 'park_categories';

    protected $fillable = ['park_id', 'category_id', 'subcategory_id', 'active'];

    public function park()
    {
        return $this->belongsTo(Parks::class, "park_id");
    }
    public function category()
    {
        return $this->belongsTo(Category::class, "category_id");
    }
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, "subcategory_id");
    }
}
