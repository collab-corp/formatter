<?php

namespace CollabCorp\Formatter\Concerns;

use CollabCorp\Formatter\Formatter;

trait ProcessesMethodCallsOnArrays
{

    /**
     * Handle method calls on
     * an input that is an array.
     * @param  array $input
     * @param  String $method
     * @param  array $params
     * @return array $newValues
     */
    protected function handleMethodCallsOnArrayInput($input, $method, $params)
    {
        $newValues = [];

        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $newValues[$key] = $this->handleMethodCallsOnArrayInput($value, $method, $params);
            } else {
                $newValues[$key] =  Formatter::call($method, $params, $value)->get();
            }
        }

        return $newValues;
    }
}
