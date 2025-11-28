<?php

namespace App\View\Components\common;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class adminHeader extends Component
{
    public $class;
    public $title;
    public function __construct($class = '', $title = 'Default Title')
    {
        $this->class = $class;
        $this->title = $title;
    }

    public function render(): View|Closure|string
    {
        return view('components.common.admin-header');
    }
}
