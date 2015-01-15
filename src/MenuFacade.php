<?php

namespace Illuminate\Html;

use Illuminate\Support\Facades\Facade;

/**
 * Class MenuFacade
 *
 * @see \Illuminate\Html\MenuBuilder
 *
 * @package Illuminate\Html
 */
class MenuFacade extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor() { return 'menu'; }
}