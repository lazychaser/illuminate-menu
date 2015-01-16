<?php

namespace Illuminate\Support\Facades;

/**
 * Class Menu
 *
 * @see \Illuminate\Html\MenuBuilder
 *
 * @package Illuminate\Support\Facades
 */
class Menu extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor() { return 'menu'; }
}