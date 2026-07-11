<?php

namespace App\View\Components\Theme\Navigation;

interface NavigationController
{
    public function items(): array;

    public function button(): array;
}
