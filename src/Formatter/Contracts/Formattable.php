<?php

namespace CollabCorp\Formatter\Contracts;

interface Formattable
{

    /**
     * Format the given value.
     *
     * @param mixed $value
     * @return mixed
     */
    public function format($value);
}
