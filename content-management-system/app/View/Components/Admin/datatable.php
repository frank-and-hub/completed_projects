<?php

namespace App\View\Components\admin;

use Illuminate\View\Component;

class datatable extends Component
{
    public $id,$title,$custom_headings,$otherClass;
    public function __construct($id,$title,$custom_headings=null,$otherClass=null,public $other=null,public $loaderID='dt-loader')
    {
        $this->id = $id;
        $this->title = $title;
        $this->custom_headings = $custom_headings;
        $this->otherClass = $otherClass;
    }


    public function render()
    {
        return view('components.admin.datatable');
    }
}
