<?php

use Carbon\Carbon;

//simply a method to use for testing custom callables.
if (!function_exists('to_carbon')) {
    function to_carbon($date)
    {
        return new Carbon($date);
    }
}
