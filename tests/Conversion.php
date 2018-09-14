<?php
namespace CollabCorp\Formatter\Tests;

use CollabCorp\Formatter\Contracts\Convertible;

class Conversion implements Convertible
{
    public function convert($value, $data)
    {
        $value = "change-me";

        return $value;
    }
}
