<?php

use Illuminate\Support\Facades\Request;

if (!function_exists('setActive')) {
    function setActive($route, $class = 'active')
    {
        return Request::is($route) ? $class : '';
    }
}
