<?php

namespace CollabCorp\Formatter\Contracts;

use Illuminate\Http\Request;

interface CheckForEmpty
{
    /**
     * Check if the given value is "empty".
     * @param  mixed  $value
     * @param  array  $data
     * @return boolean
     */
    public function isEmpty($value, $data) :bool;
}
