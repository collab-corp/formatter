<?php

namespace CollabCorp\Formatter;

use CollabCorp\Formatter\FormatterProcessor;
use CollabCorp\Formatter\Traits\HandlesDateConversions;
use CollabCorp\Formatter\Traits\HandlesMathConversions;
use CollabCorp\Formatter\Traits\HandlesStringConversions;
use Illuminate\Support\Traits\Macroable;

class Formatter
{
    use HandlesDateConversions,HandlesMathConversions,HandlesStringConversions,Macroable;

    /**
     * The value that is being formatted
     * @var mixed $value
     */
    protected $value;


    /**
     * Construct a new instance
     * @param mixed $value
     * @return CollabCorp\Formatter\Formatter
     */
    public function __construct($value = '')
    {
        if ($value === null || $value == '') {
            return null;
        }
        $this->value = trim($value);
    }

    /**
     * Cast the formatter to a string,
     * returning the result.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }

    /**
    * Get the value from the instance
    * @return mixed $value
    */
    public function get()
    {
        return $this->value;
    }

    /**
    * Get the singleton instance
    * @param  $value
    * @return CollabCorp\Formatter\Formatter
    */
    public static function singleton($value = null)
    {
        static $inst = null;

        if ($inst === null) {
            $inst = new static($value);
        } else {
            $inst->setValue($value);
        }
        return $inst;
    }
    /**
     * Create a new instance via static method
     * @param  mixed $value
     * @return CollabCorp\Formatter\Convert
     */
    public static function create($value)
    {
        $formatter = new static($value);
        return $formatter;
    }

    /**
     * Set the value
     * @param mixed $value
     * @return CollabCorp\Formatter\Formatter
     */
    public function setValue($value)
    {
        /*automatically treat empty strings as null,
        this is due to some issues with laravel's convert empty string to null middleware*/
        if ($value == '') {
            $value =  null;
        }

        $this->value = $value;

        return $this;
    }

    /**
    * Convert the input according to the formatters
    * @param  array $formatters
    * @param  array $request
    * @return Illuminate\Support\Collection
    */
    public static function convert(array $formatters, array $request)
    {
        $explictKeys = array_filter($formatters, function ($key) use ($request) {
            return array_key_exists($key, $request) || !is_null(data_get($request, $key));
        }, ARRAY_FILTER_USE_KEY);

        $patterns = array_filter($formatters, function ($key) use ($request) {
            return  !array_key_exists($key, $request) && is_null(data_get($request, $key));
        }, ARRAY_FILTER_USE_KEY);


        $request = (new FormatterProcessor())->process(
            $request,
            $explictKeys,
            $patterns
        );

        return collect($request);
    }

    /**
     * Throw an exception for non numeric values
     * @param  $method
     * @throws \Exception
     */
    protected function throwExceptionIfNonNumeric($method, $value =null)
    {
        if (is_null($value)) {
            $value = $this->value;
        }
        if (!is_numeric($value) && strlen($value)) {
            throw new \Exception("Non numeric value passed to {$method}, value given : {$value}");
        }
    }
}
