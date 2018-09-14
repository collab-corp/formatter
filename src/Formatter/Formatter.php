<?php

namespace CollabCorp\Formatter;

use CollabCorp\Formatter\Concerns\ProcessesMethodCallsOnArrays;
use CollabCorp\Formatter\ConverterManager;
use CollabCorp\Formatter\Converters\ArrayConverter;
use CollabCorp\Formatter\Exceptions\FormatterException;
use CollabCorp\Formatter\Formatter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class Formatter
{
    use ProcessesMethodCallsOnArrays,Macroable {
        __call as macroCall;
    }
    /**
    * Whitelist of the allowed methods to be called
    * @var array
    */
    protected $whiteList =[];
    /**
     * The value that is being formatted
     * @var mixed $value
     */
    protected $value;

    /**
     * The converter manager.
     * @var \CollabCorp\Formatter\ConverterManager
     */
    protected static $manager;

    /**
     * Only run formatter methods if values are not null || empty string
     * @var boolean
     */
    protected static $ignoreIfValueIsEmpty = false;


    /**
     * Only run formatter methods if the given value
     * is not an 'empty' value (empty string|null|empty array)
     * @param  bool $run
     * @return void
     */
    public static function ignoreIfValueIsEmpty(bool $run)
    {
        static::$ignoreIfValueIsEmpty = $run;
    }
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

        if ($this->value instanceof Collection || is_array($this->value)) {
            $values = [];

            foreach ($this->value as $key => $value) {
                if (is_array($value)) {
                    $values[$key] = $this->handleMethodCallsOnArrayInput($value, $method, $args);
                } else {
                    $values[$key] = static::call($method, $args, $value)->get();
                }
            }
            return new static($values);
        }

        //simple/single values
        return static::call($method, $args, $this);
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

        if (($previous instanceof Formatter || is_subclass_of($previous, Formatter::class)) && is_callable($previous)) {
            $previous = $previous();
        }



        if (!static::shouldRun($previous)) {
            return new static($previous);
        }
        $formatter = $formatter->create($previous);

        return $formatter->$method(...$parameters);
    }

    /**
     * Determine if the value is empty
     * and a formatter should run
     * via static setting
     * @param  mixed $value
     * @return bool
     */
    protected static function shouldRun($value)
    {
        if (static::$ignoreIfValueIsEmpty) {
            if ($value instanceof Collection) {
                $value = $value->all();
            }

            if (is_array($value) && empty($value)) {
                return false;
            }
            if (is_null($value) || $value === '') {
                return false;
            }
        }

        return true;
    }


    /**
     * Construct a new instance
     * @param mixed $value
     * @return CollabCorp\Formatter\Formatter
     */
    public function __construct($value = '')
    {
        $this->setValue($value);

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
        if ($this->value instanceof Collection || is_array($this->value)) {
            throw FormatterException::stringCastOnMultipleValues();
        }
        return (string) $this->get();
    }

    /**
    * Get the value(s) from the instance
    * @param  string|int $key
    * @param  mixed $def
    * @return mixed $value
    */
    public function get($key = null, $def = null)
    {
        if ($this->value instanceof Collection) {
            if (!is_null($key)) {
                return $this->value->get($key, $def);
            }
            //if no index/key was specified just return all
            return $this->value->all();
        }
        return $this->value;
    }
    /**
    * Get the first value from the instance
    * if the value is a collection instance
    * @return mixed $value
    */
    public function all()
    {
        if ($this->value instanceof Collection) {
            return $this->value->all();
        }
        return $this->value;
    }
    /**
    * Get the first value from the instance
    * if the value is a collection instance or array
    * or just return the underlying simple value
    * @return mixed $value
    */
    public function first()
    {
        if ($this->value instanceof Collection) {
            return $this->value->first();
        } elseif (is_array($this->value)) {
            return reset($this->value);
        }
        return $this->value;
    }
    /**
    * Get the last value from the instance
    * if the value is a collection instance or array
    * or just return the underlying simple value
    * @return mixed $value
    */
    public function last()
    {
        if ($this->value instanceof Collection) {
            return $this->value->last();
        } elseif (is_array($this->value)) {
            return end($this->value);
        }
        return $this->value;
    }

    /**
     * Create a new instance via static method
     * @param  mixed $value
     * @return CollabCorp\Formatter\Convert
     */
    public static function create($value)
    {
        return new static($value);
    }

    /**
     * Set the value
     * @param mixed $value
     * @return CollabCorp\Formatter\Formatter
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $this->value = collect($value);
        } elseif ($value instanceof Arrayable) {
            $this->value =  collect($value->toArray());
        } elseif (is_array($value)) {
            $this->value =  collect($value);
        } elseif (is_null($value) || $value === '') {
            /*
            Automatically treat empty strings as null, this is due to
            some issues with laravel's convert empty string to null middleware
            */
            $this->value = null;
        } else {
            $this->value = $value;
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
    * Call the formatter as a function
    *
    * @param  mixed $value
    * @return mixed
    */
    public function __invoke($value = null)
    {
        if ($value) {
            $this->setValue($value);
        }

        if ($this->value instanceof Collection) {
            return $this->all();
        }
        return $this->get();
    }


    /**
     * Get a Converter instance that implements given method
     *
     * @param  string $method
     * @return CollabCorp\Formatter\Formatter
     */
    protected static function implementing($method)
    {
        return collect(static::manager()->available())->map(function ($driver) {
            return static::manager()->driver($driver);
        })->first(function ($formatter) use ($method) {
            return method_exists($formatter, $method) && $formatter->whitelists($method);
        });
    }

    /**
    * Convert the input according to the formatters
    * @param  array $formatters
    * @param  array|mixed $request
    * @return Illuminate\Support\Collection
    */
    public static function convert(array $formatters, $request)
    {
        if (($request instanceof Request)|| $request instanceof Collection) {
            $request = $request->all();
        } elseif ($request instanceof Arrayable) {
            $request = $request->toArray();
        }

        $explictKeys = array_filter($formatters, function ($key) use ($request) {
            return array_key_exists($key, $request) || !is_null(data_get($request, $key));
        }, ARRAY_FILTER_USE_KEY);

        $patterns = array_filter($formatters, function ($key) use ($request) {
            return  !array_key_exists($key, $request) && is_null(data_get($request, $key));
        }, ARRAY_FILTER_USE_KEY);


        $request = (new FormatterProcessor($request, $explictKeys, $patterns))->get();

        return collect($request);
    }
}
