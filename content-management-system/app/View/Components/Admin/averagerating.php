<?php

namespace App\View\Components\admin;

use Illuminate\View\Component;

class averagerating extends Component
{

    public $avg_rating,$total_rating;
    public function __construct($avg_rating,$total_rating,)
    {
        $this->avg_rating = $avg_rating;
        $this->total_rating =$total_rating;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.admin.averagerating');
    }
}
