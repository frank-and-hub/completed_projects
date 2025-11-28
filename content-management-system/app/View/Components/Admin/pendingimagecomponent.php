<?php

namespace App\View\Components\admin;

use Illuminate\View\Component;

class pendingimagecomponent extends Component
{
   public $parkImages;
    public function __construct($parkImages)
    {
        $this->parkImages = $parkImages;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.admin.pendingimagecomponent');
    }
}
