<?php

namespace CollabCorp\Formatter\Traits;

trait HandlesMathConversions
{
    /**
     * Make our value be a decimal of specified places
     * @param  $numberOfPlaces
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function decimals($numberOfPlaces = 2)
    {
        $this->throwExceptionIfNonNumeric('decimals');
        $this->throwExceptionIfNonNumeric('decimals', $numberOfPlaces);

        $this->value = number_format($this->value, $numberOfPlaces, ".", "");

        return $this;
    }
    /**
     * Add a number to the numeric value
     * @param mixed $number
     * @param integer $scale
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function add($number, $scale = 0)
    {
        $this->throwExceptionIfNonNumeric('add');
        $this->throwExceptionIfNonNumeric('add', $number);
        $this->throwExceptionIfNonNumeric('add', $scale);

        $this->value = bcadd($this->value, $number, $scale);

        return $this;
    }

    /**
     * Subtract a number from the our value
     * @param mixed $number
     * @param integer $scale
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function subtract($number, $scale = 0)
    {
        $this->throwExceptionIfNonNumeric('subtract');
        $this->throwExceptionIfNonNumeric('subtract', $number);
        $this->throwExceptionIfNonNumeric('subtract', $scale);

        $this->value = bcsub($this->value, $number, $scale);

        return $this;
    }

    /**
     * Multiply our value by the given number
     * @param mixed $number
     * @param integer $scale
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function multiply($number, $scale = 0)
    {
        $this->throwExceptionIfNonNumeric('multiply');
        $this->throwExceptionIfNonNumeric('multiply', $number);
        $this->throwExceptionIfNonNumeric('multiply', $scale);
        $this->value = bcmul($this->value, $number, $scale);

        return $this;
    }
    /**
     * Raise our value the given power number
     * @param mixed $number
     * @param integer $scale
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function power($number, $scale = 0)
    {
        $this->throwExceptionIfNonNumeric('power');
        $this->throwExceptionIfNonNumeric('power', $number);
        $this->throwExceptionIfNonNumeric('power', $scale);

        $this->value = bcpow($this->value, $number, $scale);

        return $this;
    }

    /**
     * Multiply the value by the given the numeric value
     * @param mixed $number
     * @param integer $scale
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function divide($number, $scale = 0)
    {
        $this->throwExceptionIfNonNumeric('divide');
        $this->throwExceptionIfNonNumeric('divide', $number);
        $this->throwExceptionIfNonNumeric('divide', $scale);

        $this->value = bcdiv($this->value, $number, $scale);

        return $this;
    }

    /**
     * Convert our number to a percentage
     * @param integer $scale
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function percentage($scale = 2)
    {
        $this->throwExceptionIfNonNumeric('percentage');
        $this->throwExceptionIfNonNumeric('percentage', $scale);

        $this->value = $this->divide(100, $scale)->get();

        return $this;
    }
}
