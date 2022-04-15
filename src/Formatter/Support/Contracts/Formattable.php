<?php

namespace CollabCorp\Formatter\Support\Contracts;

use Closure;

interface Formattable
{

    /**
     * Format the given value.
     *
     * @param mixed $value
     * @param Closure $exit
     * @return mixed
     */
    public function format($value, Closure $exit);
}
