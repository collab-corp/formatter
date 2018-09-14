<?php

namespace CollabCorp\Formatter\Contracts;

use Illuminate\Http\Request;

interface Convertible
{
    /**
     * Convert the given value as needed.
     * @param  mixed  $value
     * @param  array $data
     * @return mixed
     */
    public function convert($value, $data);
}
