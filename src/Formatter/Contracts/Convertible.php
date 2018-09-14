<?php

namespace CollabCorp\Formatter\Contracts;

use Illuminate\Http\Request;

interface Convertible
{
    /**
     * Convert the given value as needed.
     * @param  mixed  $value
     * @param  Illuminate\Http\Request $request
     * @return mixed
     */
    public function convert($value, Request $request);
}
