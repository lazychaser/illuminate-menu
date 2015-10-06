<?php

namespace Illuminate\Html;

class Helpers
{
    /**
     * @param string $icon
     *
     * @return string
     */
    static public function icon($icon)
    {
        return '<span class="glyphicon glyphicon-'.$icon.'"></span>';
    }

    /**
     * @param string $badge
     *
     * @return string
     */
    static public function badge($badge)
    {
        return '<span class="badge">'.$badge.'</span>';
    }
}