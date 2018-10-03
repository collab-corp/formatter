<?php

use CollabCorp\Formatter\Formatter;

if (!function_exists('formatter')) {
    /**
     * Return a formatter instance
     * @param  string|null $value
     * @return CollabCorp\Formatter\Formatter
     * */
    function formatter($value=null)
    {
        return new Formatter($value);
    }
}
