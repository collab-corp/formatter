<?php
namespace CollabCorp\Formatter;

use CollabCorp\Formatter\Contracts\Convertible;
use Illuminate\Http\Request;

class Conversion implements Convertible
{
    public function convert($value, Request $request)
    {
        # code...
    }
}
