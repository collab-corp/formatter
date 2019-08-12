<?php

use Carbon\Carbon;

//simply a method to use for testing custom callables.
if (function_exists('carbon')) {
    function carbon($date)
    {
        return new Carbon($date);
    }
}
