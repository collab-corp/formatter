<?php

namespace CollabCorp\LaravelInputFormatter;

use CollabCorp\Formatter\Formatter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class DataFormatter extends Formatter
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * Dynamically call method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return parent::create($this->macroCall($method, $parameters)->get(), $parameters);
        }
        return parent::create(parent::call($method, $this->get(), $parameters));
    }
    /**
    * Convert the input according to the converters.
    * @param  array|mixed $data
    * @param  array $converters
    * @return Illuminate\Support\Collection
    */
    public static function convert($data, array $converters)
    {
        if (($data instanceof Request)|| $data instanceof Collection) {
            $data = $data->all();
        } elseif ($data instanceof Arrayable) {
            $data = $data->toArray();
        }
        return Formatter::convert($data, $converters);
    }

    /**
     * Add the given value to the end
     * our value if it does not already
     * end with the value.
     * @param string $value
     * @param string $finish
     * @return static
     */
    protected function finish($value, $finish)
    {
        return Str::finish($value, $finish);
    }
    /**
     * Add the given value to the start
     * our value if it does not already
     * start with the value.
     * @param string $value
     * @param string $start
     * @return static
     */
    protected function start($value, $start)
    {
        return Str::start($value, $start);
    }
    /**
     * Get everything before the specified before
     * value and return that.
     * @param string $value
     * @param string $before
     * @return static
     */
    protected function before($value, $before)
    {
        return Str::before($value, $before);
    }
    /**
     * Get everything after the specified after
     * value and return that.
     * @param string $value
     * @param string $after
     * @return static
     */
    protected function after($value, $after)
    {
        return Str::after($value, $after);
    }

    /**
     * Convert the value to camel case string.
     * @param string value
     * @return static
     */
    protected function camelCase($value)
    {
        return Str::camel($value);
    }
    /**
     * Convert the value to a slug string.
     * @param string value
     * @return static
     */
    protected function slug($value)
    {
        return Str::slug($value);
    }
    /**
     * Convert the value to a snake case string.
     * @param string value
     * @return static
     */
    protected function snakeCase($value)
    {
        return Str::snake($value);
    }
    /**
     * Convert the value to a studly case string.
     * @param string value
     * @return static
     */
    protected function studlyCase($value)
    {
        return Str::studly($value);
    }
    /**
     * Convert the value to a title case string.
     * @param string value
     * @return static
     */
    protected function titleCase($value)
    {
        return Str::title($value);
    }
    /**
     * Convert the value to a kebab case string.
     * @param string value
     * @return static
     */
    protected function kebabCase($value)
    {
        return Str::kebab($value);
    }
    /**
     * Convert the value to a plural version of
     * the given string value.
     * @param string value
     * @return static
     */
    protected function plural($value)
    {
        return Str::plural($value);
    }
    /**
     * Convert the value to a singular version of
     * the given string value.
     * @param string value
     * @return static
     */
    protected function singular($value)
    {
        return Str::singular($value);
    }
    /**
     * Convert the value to an encrypted value.
     * @param string value
     * @return static
     */
    protected function encrypt($value)
    {
        return encrypt($value);
    }
    /**
     * Convert the value to an decrypted value.
     * @param string value
     * @return static
     */
    protected function decrypt($value)
    {
        return decrypt($value);
    }
    /**
     * Convert the value to an bcrypted/hashed value.
     * @param string value
     * @return static
     */
    protected function bcrypt($value)
    {
        return bcrypt($value);
    }
    /**
     * Convert the value to an url value.
     * @param string value
     * @return static
     */
    protected function url($value)
    {
        return url($value);
    }
    /**
     * Replace all the given characters with the given character
     * from our given value.
     * @param  string $value
     * @param  string $search
     * @param  string $replace
     * @return static
     */
    protected function replace($value, $search, $replace = '')
    {
        return str_replace($search, $replace, $value);
    }
}
