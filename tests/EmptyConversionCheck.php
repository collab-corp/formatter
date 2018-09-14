<?php

namespace CollabCorp\Formatter\Tests;

use CollabCorp\Formatter\Contracts\CheckForEmpty;

class EmptyConversionCheck implements CheckForEmpty
{
    /**
     * Check if the given value is "empty"
     * @param  mixed  $value
     * @param  array  $data
     * @return boolean
     */
    public function isEmpty($value, $data):bool
    {
        if ($value == 'ignore me') {
            return true;
        }
        return false;
    }
}
