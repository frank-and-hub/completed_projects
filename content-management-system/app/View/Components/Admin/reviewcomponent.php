<?php

namespace App\View\Components\admin;

use Illuminate\View\Component;

class reviewcomponent extends Component
{

    public $ratings;
    public function __construct($ratings)
    {
         $this->ratings = $ratings;
    }


    public function render()
    {
        return view('components.admin.reviewcomponent');
    }
}
