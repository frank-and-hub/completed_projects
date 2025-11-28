<?php

namespace App\View\Components\admin;

use Illuminate\View\Component;

class Actioncomponent extends Component
{
    public $deleteRoute, $ShowChlidRoute, $imageUplodRoute, $imageEditRoute, $statusRoute, $detailsRoute, $id;
    public $changePasswordRoute, $detailsRouteTooltipTitle, $tooltipTitle, $jsEvent, $infoBtn;
    public $other;
    public function __construct(
        $deleteRoute = null,
        $ShowChlidRoute = null,
        $imageUplodRoute = null,
        $imageEditRoute = null,
        $statusRoute = null,
        $detailsRoute = null,
        $id = null,
        $changePasswordRoute = null,
        $detailsRouteTooltipTitle = "Show",
        $tooltipTitle = null,
        $jsEvent = null,
        $infoBtn = null,
        $other = null,
    ) {
        $this->deleteRoute = $deleteRoute;
        $this->ShowChlidRoute = $ShowChlidRoute;
        $this->imageUplodRoute = $imageUplodRoute;
        $this->imageEditRoute = $imageEditRoute;
        $this->statusRoute = $statusRoute;
        $this->id = $id;
        $this->detailsRoute = $detailsRoute;
        $this->changePasswordRoute = $changePasswordRoute;
        $this->detailsRouteTooltipTitle = $detailsRouteTooltipTitle;
        $this->tooltipTitle = $tooltipTitle;
        $this->jsEvent = $jsEvent;
        $this->infoBtn = $infoBtn;
        $this->other = $other;
    }


    public function render()
    {
        return view('components.admin.actioncomponent');
    }
}
