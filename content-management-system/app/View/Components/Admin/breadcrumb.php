<?php

namespace App\View\Components\admin;

use Illuminate\View\Component;

class breadcrumb extends Component
{
public $active,$breadcrumbs,$headerButtonRoute,$headerButton,$headerSeasonBtn;

    public function __construct($active,$breadcrumbs=null,$headerButtonRoute=null,$headerButton=null,$headerSeasonBtn=null)
    {
        $this->active = $active;
        $this->breadcrumbs = $breadcrumbs;
        $this->headerButtonRoute = $headerButtonRoute;
        $this->headerButton = $headerButton;
        $this->headerSeasonBtn = $headerSeasonBtn;

    }


    public function render()
    {
        return view('components.admin.breadcrumb');
    }
}
