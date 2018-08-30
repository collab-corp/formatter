<?php

namespace CollabCorp\Formatter\Converters;

use Carbon\Carbon;
use CollabCorp\Formatter\Exceptions\FormatterException;
use CollabCorp\Formatter\Formatter;

class DateConverter extends Formatter
{
    /**
    * Whitelist of the allowed methods to be called on this class.
    * @var Array $whiteList
    */
    protected $whiteList =[
        //Date methods
        'toCarbon',
        'setTimezone',
        'format',
        'addYears',
        'addMonths',
        'addWeeks',
        'addDays',
        'addHours',
        'addMinutes',
        'addSeconds',
        'subYears',
        'subMonths',
        'subWeeks',
        'subDays',
        'subHours',
        'subMinutes',
        'subSeconds'
    ];
    /**
     * Convert our carbon instance to a date format
     * @param String $dateFormat
     * @return this
     */
    public function format($dateFormat)
    {
        $this->value = $this->value->format($dateFormat);

        return $this;
    }


    /**
     * Convert our value to a carbon instance
     * @return  this
     */
    public function toCarbon()
    {
        $this->value = Carbon::parse($this->value);

        return $this;
    }

    /**
    * Set a timezone on our carbon instance
    * @return  this
    */
    public function setTimezone($tz)
    {
        $this->value = $this->value->setTimezone($tz);

        return $this;
    }
    /**
     * Add years to our carbon instance
     * @param mixed $years
     * @return this
     */
    public function addYears($years)
    {
        $this->value = $this->value->addYears($years);

        return $this;
    }
    /**
     * Add months to our carbon instance
     * @param mixed $months
     * @return this
     */
    public function addMonths($months)
    {
        $this->value = $this->value->addMonths($months);

        return $this;
    }
    /**
     * Add weeks to our carbon instance
     * @param mixed $weeks
     * @return this
     */
    public function addWeeks($weeks)
    {
        $this->value = $this->value->addWeeks($weeks);

        return $this;
    }

    /**
    * Add days to our carbon instance
    * @param mixed $days
    * @return this
    */
    public function addDays($days)
    {
        $this->value = $this->value->addDays($days);


        return $this;
    }

    /**
    * Add hours to our carbon instance
    * @param mixed $hours
    * @return this
    */
    public function addHours($hours)
    {
        $this->value = $this->value->addHours($hours);

        return $this;
    }

    /**
     * Add minutes to our carbon instance
     * @param mixed $minutes
     * @return this
     */
    public function addMinutes($minutes)
    {
        $this->value = $this->value->addMinutes($minutes);

        return $this;
    }
    /**
    * Add seconds to our carbon instance
    * @param mixed $seconds
    * @return this
    */
    public function addSeconds($seconds)
    {
        $this->value = $this->value->addSeconds($seconds);

        return $this;
    }

    /**
     * Subtract years from our carbon instance
     * @param mixed $years
     * @return this
     */
    public function subYears($years)
    {
        $this->value = $this->value->subYears($years);

        return $this;
    }

    /**
    * Subtract months to our carbon instance
    * @param mixed $months
    * @return this
    */
    public function subMonths($months)
    {
        $this->value = $this->value->subMonths($months);

        return $this;
    }
    /**
     * Subtract weeks to our carbon instance
     * @param mixed $weeks
     * @return this
     */
    public function subWeeks($weeks)
    {
        $this->value = $this->value->subWeeks($weeks);

        return $this;
    }

    /**
    * Subtract days to our carbon instance
    * @param mixed $days
    * @return this
    */
    public function subDays($days)
    {
        $this->value = $this->value->subDays($days);

        return $this;
    }

    /**
    * Subtract hours to our carbon instance
    * @param mixed $hours
    * @return this
    */
    public function subHours($hours)
    {
        $this->value = $this->value->subHours($hours);

        return $this;
    }

    /**
     * Subtract minutes to our carbon instance
     * @param mixed $minutes
     * @return this
     */
    public function subMinutes($minutes)
    {
        $this->value = $this->value->subMinutes($minutes);

        return $this;
    }
    /**
    * Subtract seconds to our carbon instance
    * @param mixed $seconds
    * @return this
    */
    public function subSeconds($seconds)
    {
        $this->value = $this->value->subSeconds($seconds);

        return $this;
    }
}
