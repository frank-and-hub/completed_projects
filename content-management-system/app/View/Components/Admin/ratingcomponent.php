<?php

namespace App\View\Components\admin;

use Illuminate\View\Component;

class ratingcomponent extends Component
{
   public $rating;
    public function __construct($rating)
    {
        $this->rating = $rating;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.admin.ratingcomponent');
    }
}
