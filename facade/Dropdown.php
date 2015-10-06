<?php

namespace Illuminate\Support\Facades;

use Illuminate\Html\DropdownBuilder;

/**
 * Class Menu
 *
 * @see     \Illuminate\Html\DropdownBuilder
 *
 * @package Illuminate\Support\Facades
 */
class Dropdown extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return DropdownBuilder::class;
    }
}