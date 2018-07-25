<?php

namespace CollabCorp\Formatter\Converters;

use CollabCorp\Formatter\Formatter;

class MathConverter extends Formatter
{
    /**
    * Whitelist of the allowed methods to be called on this class.
    * @var Array $whiteList
    */
    protected $whiteList =[
        //Math methods
        'decimals',
        'add',
        'subtract',
        'multiply',
        'divide',
        'power',
        'percentage'
    ];
    /**
     * Make our value be a decimal of specified places
     * @param  $numberOfPlaces
     * @return self
     */
    public function decimals($numberOfPlaces = 2)
    {
        $this->value = number_format($this->value, $numberOfPlaces, ".", "");

        return $this;
    }
    /**
     * Add a number to the numeric value
     * @param mixed $number
     * @return self
     */
    public function add($number)
    {
        if (!function_exists('bcadd')) {
            $this->value = $this->value + $number;
        } else {
            $this->value = bcadd($this->value, $number, 64);
        }

        return $this;
    }

    /**
     * Subtract a number from the our value
     * @param mixed $number
     * @return self
     */
    public function subtract($number)
    {
        if (!function_exists('bcsub')) {
            $this->value = $this->value - $number;
        } else {
            $this->value = bcsub($this->value, $number, 64);
        }


        return $this;
    }

    /**
     * Multiply our value by the given number
     * @param mixed $number
     * @return self
     */
    public function multiply($number)
    {
        if (!function_exists('bcmul')) {
            $this->value = $this->value * $number;
        } else {
            $this->value = bcmul($this->value, $number, 64);
        }

        return $this;
    }
    /**
     * Raise our value the given power number
     * @param mixed $number
     * @return self
     */
    public function power($number)
    {
        if (!function_exists('bcpow')) {
            $this->value = $this->value ** $number;
        } else {
            $this->value = bcpow($this->value, $number, 64);
        }


        return $this;
    }

    /**
     * Multiply the value by the given the numeric value
     * @param mixed $number
     * @return self
     */
    public function divide($number)
    {
        if (!function_exists('bcdiv')) {
            $this->value = $this->value / $number;
        } else {
            $this->value = bcdiv($this->value, $number, 64);
        }


        return $this;
    }

    /**
     * Convert our number to a percentage
     * @return self
     */
    public function percentage()
    {
        $this->value = $this->divide(100)->get();

        return $this;
    }
}
