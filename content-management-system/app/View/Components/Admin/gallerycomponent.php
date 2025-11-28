<?php

namespace App\View\Components\admin;

use Illuminate\View\Component;

class gallerycomponent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $parkimages;
    public function __construct($parkimages=null)
    {
        $this->parkimages = $parkimages;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.admin.gallerycomponent');
    }
}
