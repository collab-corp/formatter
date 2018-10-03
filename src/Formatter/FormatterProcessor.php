<?php

namespace CollabCorp\Formatter;

use CollabCorp\Formatter\Concerns\ProcessesMethodCallsOnArrays;
use CollabCorp\Formatter\Contracts\CheckForEmpty;
use CollabCorp\Formatter\Contracts\Convertible;
use CollabCorp\Formatter\Conversion;
use CollabCorp\Formatter\Formatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FormatterProcessor
{
    use ProcessesMethodCallsOnArrays;

    /**
    * Construct a new formatter processor instance
    * @param array $input
    * @param array $explicitAttributes
    * @param array $wildCardAttributes
    * @return static
    */
    public function __construct(array $input, array $explicitAttributes, array $wildCardAttributes)
    {
        $this->data = $input;

        $this->explicitAttributes = $explicitAttributes;

        $this->wildCardAttributes = $wildCardAttributes;


        $this->convertExplicitAttributes();

        $this->convertWildCardAttributes();


        return $this;
    }

    /**
     * Get the processed data
     * @return array
     */
    public function get()
    {
        return $this->data;
    }

    /**
    * Explode the explicit formatter into an array if necessary.
    *
    * @param  mixed  $formatter
    * @return array
    */
    protected function explodeMethodsToCall($formatter)
    {
        if (is_string($formatter)) {
            return explode('|', trim($formatter, "|"));
        }

        return is_array($formatter)? $formatter:[$formatter];
    }
    /**
    * Call a formatter method on the given attribute
    * using the formatter being processed.
    *
    * @param  string attribute
    * @param  mixed  $formatter
    * @return mixed
    */
    protected function callMethodOnAttribute($attribute, $formatter)
    {
        $input =  data_get($this->data, $attribute);

        if ($formatter instanceof \Closure) {
            data_set($this->data, $attribute, call_user_func_array($formatter, [$input, $this->data]));
        } elseif ($formatter instanceof Convertible) {
            data_set($this->data, $attribute, $formatter->convert($input, $this->data));
        } elseif ((is_array($input) || $input instanceof Collection) && is_string($formatter)) {
            $details = $this->extractFormatterDetails($formatter);
            $this->processMethodCallOnArrayInput($attribute, $details, $input);
        } elseif (is_string($formatter)) {
            $details = $this->extractFormatterDetails($formatter);

            $this->processMethodCall($attribute, $details, $input);
        }
        // return  $formatter;
    }

    /**
     * Process method call on array/collection
     * input.
     * @param  string $attribute
     * @param  array  $details
     * @param  mixed  $input
     * @return static
     */
    protected function processMethodCallOnArrayInput($attribute, array $details, $input)
    {
        $isCollection = false;

        if ($input instanceof Collection) {
            $isCollection = true;
            $input = $input->all();
        }

        $this->data[$attribute] = $this->handleMethodCallsOnArrayInput($input, $details[0], $details[1]);

        //if we were originally working with a collection make it a collection again.
        if ($isCollection) {
            $this->data[$attribute] =collect($this->data[$attribute]);
        }
    }

    /**
     * Process method call on simple values
     * @param  string $attribute
     * @param  array  $details
     * @param  mixed  $input
     * @return static
     */
    protected function processMethodCall($attribute, array $details, $input)
    {
        $input = data_get($this->data, $attribute);

        if (!is_null($input) && strpos($attribute, ".") !== false) {
            data_set($this->data, $attribute, Formatter::call($details[0], $details[1], $input)->get());
        } else {
            $this->data[$attribute] = Formatter::call($details[0], $details[1], $this->data[$attribute])->get();
        }
    }

    /**
     * Extract the method/parameters
     * from a string based formatter
     * @param  string  $formatters
     * @return array
     * @see  Illuminate\Validation\ValidationRuleParser@parseStringRule
     */
    protected static function extractFormatterDetails($formatter)
    {
        $parameters = [];

        // {method}:{parameters}
        if (strpos($formatter, ':') !== false) {
            list($formatter, $parameter) = explode(':', $formatter);

            $parameters = str_getcsv($parameter);
        }

        return [(trim($formatter)), $parameters];
    }
    /**
     * Determine if the given value needs to be
     * skipped in mass conversions
     * @param  mixed $value
     * @return bool
     */
    protected function bailIfEmpty($method, $value)
    {
        if (is_string($method) && $method == 'bailIfEmpty') {
            if ($value instanceof Collection && $value->isEmpty()) {
                return true;
            }
            if (is_array($value) && empty($value)) {
                return true;
            }
            if (is_null($value) || $value == '') {
                return true;
            }
        } elseif ($method instanceof CheckForEmpty) {
            return $method->isEmpty($value, $this->data);
        }


        return false;
    }
    /**
      * Convert explicit attributes
      * @return static
      */
    protected function convertExplicitAttributes()
    {
        $explicitAttributes = collect($this->explicitAttributes);

        $explicitAttributes->each(function ($methods, $attribute) {
            $methods = collect($this->explodeMethodsToCall($methods));
            $methods->each(function ($method) use ($attribute) {
                //bail out as needed or told
                $bail = $this->bailIfEmpty($method, data_get($this->data, $attribute));

                if ($bail) {
                    return false;
                } elseif (!$bail && $method !='bailIfEmpty') {
                    $this->callMethodOnAttribute($attribute, $method);
                }
            });
        });

        return $this;
    }


    /**
    * Convert wildcard attributes
    * @return static
    */
    protected function convertWildCardAttributes()
    {
        $wildCardAttributes = collect($this->wildCardAttributes);

        $wildCardAttributes->each(function ($methods, $attribute) {
            //determine attributes that match patterns
            $matches = collect($this->data)->filter(function ($value, $key) use ($attribute) {
                return Str::is($attribute, $key);
            })->keys()->each(function ($attr) use ($methods) {
                //then iterate through the attributes and process formatters
                $methods = collect($this->explodeMethodsToCall($methods))->each(function ($method) use ($attr) {
                    //bail out as needed or told
                    $bail = $this->bailIfEmpty($method, data_get($this->data, $attr));

                    if ($bail) {
                        return false;
                    } elseif (!$bail && $method !='bailIfEmpty') {
                        $this->callMethodOnAttribute($attr, $method);
                    }
                });
            });
        });

        return $this;
    }
}
