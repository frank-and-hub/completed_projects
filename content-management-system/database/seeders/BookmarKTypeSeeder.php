<?php

namespace Database\Seeders;

use App\Models\BookmarkType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookmarKTypeSeeder extends Seeder
{

    public function run()
    {
        $types =['Want to go','Favourites'];
        foreach($types as $type){
            BookmarkType::updateOrCreate(['type'=>$type],['type'=>$type]);
        }
    }
}
