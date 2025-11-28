<?php

namespace App\View\Components\admin\park;

use Illuminate\View\Component;

class selectoptioncomponent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $parkscapeuploadedimage;
    public $subadminuplodedimage;
    public $useruploadedimage;
    public function __construct($parkscapeuploadedimage=null,$subadminuplodedimage=null,$useruploadedimage=null)
    {
        $this->parkscapeuploadedimage = $parkscapeuploadedimage;
        $this->subadminuplodedimage = $subadminuplodedimage;
        $this->useruploadedimage = $useruploadedimage;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.admin.park.selectoptioncomponent');
    }
}
