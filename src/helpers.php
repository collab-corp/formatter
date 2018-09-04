<?php

use CollabCorp\Formatter\Formatter;

if (!function_exists('formatter')) {
    /**
     * Return a formatter instance via
     * key binding and set the given value.
     * @param  string $value
     * @return $value|CollabCorp\Formatter\Formatter
     * */
    function formatter($value='')
    {
        if (!app()->bound('collab-corp.formatter')) {
            return new Formatter($value);
        }
        return app('collab-corp.formatter')->setValue($value);
    }
}
