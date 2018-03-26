<?php

namespace CollabCorp\Formatter;

use CollabCorp\Formatter\ConverterManager;
use CollabCorp\Formatter\Exceptions\FormatterException;
use Illuminate\Support\Traits\Macroable;

class Formatter
{
    use Macroable {
        __call as macroCall;
    }
     /**
     * Whitelist of the allowed methods to be called
     * @var array $whiteList
     */
    protected $whiteList =[];
    /**
     * The value that is being formatted
     * @var mixed $value
     */
    protected $value;

    /**
     * the converter manager.
     * @var \CollabCorp\Formatter\ConverterManager
     */
    protected static $manager;

    /**
     * Call macros of proxy calls to other formatters.
     *
     * @param  String $method
     * @param  array $args
     * @return CollabCorp\Formatter\Formatter
     */
    public function __call($method, $args = [])
    {
        if (static::hasMacro($method)) {

            $this->setValue($this->macroCall($method, $args)->get());

            return $this;
        }
        return static::call($method, $args, $this);
    }
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
        $this->value = $value;

        return $this;
    }

    /**
     * Reset the value to null.
     *
     * @return $this
     */
    public function clear()
    {
        $this->value = null;
        return $this;
    }

    /**
     * Cast the formatter to a string,
     * returning the result.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->get();
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

        $this->value = $value;
        /*automatically treat empty strings as null,
        this is due to some issues with laravel's convert empty string to null middleware*/
        if ($value == '') {
            return $this->clear();
        }



        return $this;
    }
    /**
     * Determine if the method is allowed to be called
     *
     * @param  String $method
     * @return boolean
     */
    public function whitelists($method)
    {
        if (!property_exists($this, 'whiteList')) {
            $class = get_class($this);
            throw new \Exception("$class must have a whitelist property");
        }
        return in_array($method, $this->whiteList);
    }
    /**
     * Get the ConverterManager.
     *
     * @return \CollabCorp\Formatter\ConverterManager
     */
    public static function manager()
    {
        if (static::$manager) {
            return static::$manager;
        }
        return static::$manager = new ConverterManager(app());
    }
     /**
     * This allows the Formatter to be called as a function.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function __invoke($value = null)
    {
        if ($value) {
            $this->setValue($value);
        }
        return $this->get();
    }

    /**
     * Proxy the call into the first formatter that can handle it.
     *
     * @param  string $method
     * @param  mixed $parameters
     * @param  object | null $previous [The previous formatter or value]
     *
     * @throws \CollabCorp\Formatter\Exceptions\FormatterException
     * @return mixed
     */
    public static function call($method, $parameters, $previous = null)
    {

        $formatter = Formatter::implementing($method);

        throw_if(is_null($formatter), FormatterException::notFound($method));


        return $formatter->create(is_callable($previous) ? $previous() : $previous)->$method(...$parameters);
    }

    /**
     * Get a Formatter instance that implements given method
     *
     * @param  string $method
     * @return \CollabCorp\Formatter\Formatters\BaseFormatter
     */
    public static function implementing($method)
    {
        return collect(static::manager()->available())->map(function ($driver) {
            return static::manager()->driver($driver);
        })->first(function ($formatter) use($method) {
            return method_exists($formatter, $method) && $formatter->whitelists($method);
        });
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

}
