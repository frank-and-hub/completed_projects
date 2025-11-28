<?php

namespace App\View\Components\common;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class linkButton extends Component
{
    public $justify;
    public $href;
    public $buttonType;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($justify = 'center', $href = '#', $buttonType = 'primary-button')
    {
        $this->justify = $justify;
        $this->href = $href;
        $this->buttonType = $buttonType;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.common.link-button');
    }
}
