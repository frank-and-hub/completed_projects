<?php

namespace App\View\Components\admin;

use Illuminate\View\Component;

class Dashboardcard extends Component
{
    public $color,$title,$id,$count, $icon,$route;
    public function __construct($title=null,$color=null,$id=null,$count=null,$icon=null,$route=null)
    {
        $this->color = $color;
        $this->id = $id;
        $this->title = $title;
        $this->count = $count;
        $this->icon = $icon;
        $this->route = $route;
    }


    public function render()
    {
        return view('components.admin..dashboard.dashboardcard');
    }
}
