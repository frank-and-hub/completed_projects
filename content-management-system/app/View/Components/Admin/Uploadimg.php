<?php

namespace App\View\Components\admin;

use Illuminate\View\Component;

class Uploadimg extends Component
{
    public $imgpath, $imgdeletelink, $other, $id,$defaultimgurl,$imageSizeWarning;
    public function __construct($imgpath = null, $imgdeletelink = null, $other = null, $id = null,
    $defaultimgurl=null,$imageSizeWarning=null)
    {
        $this->imgpath = $imgpath;
        $this->imgdeletelink = $imgdeletelink;
        $this->other = $other;
        $this->id = $id;
        $this->defaultimgurl = $defaultimgurl;
        $this->imageSizeWarning = $imageSizeWarning;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.admin.uploadimg');
    }
}
