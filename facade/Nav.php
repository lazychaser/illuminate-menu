<?php

namespace Illuminate\Support\Facades;

use Illuminate\Html\NavBuilder;

/**
 * Class Menu
 *
 * @see     \Illuminate\Html\NavBuilder
 *
 * @package Illuminate\Support\Facades
 */
class Nav extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return NavBuilder::class;
    }
}