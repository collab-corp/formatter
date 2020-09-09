<?php

namespace CollabCorp\Formatter\Support;

use Closure;
use CollabCorp\Formatter\Support\Contracts\Formattable;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationRuleParser;

class CallableParser extends ValidationRuleParser
{
    /**
     * Parse an array based callable.
     *
     * @param  array  $callables
     * @return array
     */
    protected static function parseArrayRule(array $callables)
    {
        return [trim(Arr::get($callables, 0)), array_slice($callables, 1)];
    }

    /**
     * Parse a string based callable.
     *
     * @param  string  $callables
     * @return array
     */
    protected static function parseStringRule($callables)
    {
        $parameters = [];

        if (strpos($callables, ':') !== false) {
            [$callables, $parameter] = explode(':', $callables, 2);

            $parameters = static::parseParameters($callables, $parameter);
        }

        return [trim($callables), $parameters];
    }

    /**
     * Check if the given value is a Formattable or Closure.
     * @param  mixed  $callable
     * @return boolean
     */
    public static function isFormattableOrClosure($callable)
    {
        return $callable instanceof Formattable || $callable instanceof Closure;
    }

    /**
    * Prepare the given callable for parsing.
    *
    * @param  mixed  $callable
    * @return mixed
    */
    protected function prepareRule($callable)
    {
        if (is_string($callable) || static::isFormattableOrClosure($callable)) {
            return $callable;
        }

        return parent::prepareRule($callable);
    }

     /**
     * Extract the callable name and parameters from a callable.
     *
     * @param  array|string  $callables
     * @return array
     */
    public static function parse($callables)
    {
        if (static::isFormattableOrClosure($callables)) {
            return [$callables, []];
        }

        return parent::parse($callables);
    }
}
