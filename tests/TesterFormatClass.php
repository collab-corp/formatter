<?php

namespace CollabCorp\Formatter\Tests;

use CollabCorp\Formatter\Contracts\Formattable;

class TesterFormatClass implements Formattable
{
    /**
     * Format the value.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function format($value)
    {
        return $value."@@@@@hhhhh";
    }
}
