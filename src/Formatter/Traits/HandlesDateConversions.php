<?php

namespace CollabCorp\Formatter\Traits;

use Carbon\Carbon;

trait HandlesDateConversions
{
    /**
     * Convert our carbon instance to a date format
     * @param String $dateFormat
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function format($dateFormat)
    {
        $this->value = $this->value->format($dateFormat);

        return $this;
    }

    /**
     * Convert our value to a carbon instance
     * @return  CollabCorp\Formatter\Formatter instance
     */
    public function toCarbon()
    {
        $this->value = Carbon::parse($this->value);


        return $this;
    }

    /**
    * Set a timezone on our carbon instance
    * @return  CollabCorp\Formatter\Formatter instance
    */
    public function setTimezone($tz)
    {
        $this->value = $this->value->setTimezone($tz);


        return $this;
    }
    /**
     * Add years to our carbon instance
     * @param mixed $years
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function addYears($years)
    {
        $this->throwExceptionIfNonNumeric('addYears', $years);
        $this->value = $this->value->addYears($years);

        return $this;
    }
    /**
     * Add months to our carbon instance
     * @param mixed $months
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function addMonths($months)
    {
        $this->throwExceptionIfNonNumeric('addMonths', $months);
        $this->value = $this->value->addMonths($months);

        return $this;
    }
    /**
     * Add weeks to our carbon instance
     * @param mixed $weeks
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function addWeeks($weeks)
    {
        $this->throwExceptionIfNonNumeric('addWeeks', $weeks);
        $this->value = $this->value->addWeeks($weeks);

        return $this;
    }

    /**
    * Add days to our carbon instance
    * @param mixed $days
    * @return CollabCorp\Formatter\Formatter instance
    */
    public function addDays($days)
    {
        $this->throwExceptionIfNonNumeric('addDays', $days);
        $this->value = $this->value->addDays($days);


        return $this;
    }

    /**
    * Add hours to our carbon instance
    * @param mixed $hours
    * @return CollabCorp\Formatter\Formatter instance
    */
    public function addHours($hours)
    {
        $this->throwExceptionIfNonNumeric('addHours', $hours);
        $this->value = $this->value->addHours($hours);

        return $this;
    }

    /**
     * Add minutes to our carbon instance
     * @param mixed $minutes
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function addMinutes($minutes)
    {
        $this->throwExceptionIfNonNumeric('addMinutes', $minutes);
        $this->value = $this->value->addMinutes($minutes);

        return $this;
    }
    /**
    * Add seconds to our carbon instance
    * @param mixed $seconds
    * @return CollabCorp\Formatter\Formatter instance
    */
    public function addSeconds($seconds)
    {
        $this->throwExceptionIfNonNumeric('addSeconds', $seconds);

        $this->value = $this->value->addSeconds($seconds);

        return $this;
    }

    /**
     * Subtract years from our carbon instance
     * @param mixed $years
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function subYears($years)
    {
        $this->throwExceptionIfNonNumeric('subYears', $years);

        $this->value = $this->value->subYears($years);

        return $this;
    }

    /**
    * Subtract months to our carbon instance
    * @param mixed $months
    * @return CollabCorp\Formatter\Formatter instance
    */
    public function subMonths($months)
    {
        $this->throwExceptionIfNonNumeric('subMonths', $months);

        $this->value = $this->value->subMonths($months);

        return $this;
    }
    /**
     * Subtract weeks to our carbon instance
     * @param mixed $weeks
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function subWeeks($weeks)
    {
        $this->throwExceptionIfNonNumeric('subWeeks', $weeks);
        $this->value = $this->value->subWeeks($weeks);

        return $this;
    }

    /**
    * Subtract days to our carbon instance
    * @param mixed $days
    * @return CollabCorp\Formatter\Formatter instance
    */
    public function subDays($days)
    {
        $this->throwExceptionIfNonNumeric('subDays', $days);
        $this->value = $this->value->subDays($days);

        return $this;
    }

    /**
    * Subtract hours to our carbon instance
    * @param mixed $hours
    * @return CollabCorp\Formatter\Formatter instance
    */
    public function subHours($hours)
    {
        $this->throwExceptionIfNonNumeric('subHours', $hours);
        $this->value = $this->value->subHours($hours);

        return $this;
    }

    /**
     * Subtract minutes to our carbon instance
     * @param mixed $minutes
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function subMinutes($minutes)
    {
        $this->throwExceptionIfNonNumeric('subMinutes', $minutes);
        $this->value = $this->value->subMinutes($minutes);

        return $this;
    }
    /**
    * Subtract seconds to our carbon instance
    * @param mixed $seconds
    * @return CollabCorp\Formatter\Formatter instance
    */
    public function subSeconds($seconds)
    {
        $this->throwExceptionIfNonNumeric('subSeconds', $seconds);
        $this->value = $this->value->subSeconds($seconds);

        return $this;
    }
}
