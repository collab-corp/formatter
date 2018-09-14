<?php

namespace CollabCorp\Formatter;

use CollabCorp\Formatter\Concerns\ProcessesMethodCallsOnArrays;
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
    * @param array $request
    * @param array $explicitKeys
    * @param array $pattern
    * @param array $request
    * @return $this
    */
    public function __construct(array $request, array $explicitKeys, array $patterns)
    {
        $this->data = $request;

        $this->explicitKeys = $explicitKeys;

        $this->patterns = $patterns;


        $request = $this->convertExplicitKeys($request, $explicitKeys);

        dd("end");
        return $this;
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
        } elseif (is_object($formatter)) {
            return [$formatter];
        }

        return [(string) $formatter];
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
        if ($formatter instanceof \Closure) {
        } elseif ($formatter instanceof Convertible) {
            dd("is conversion object");
        }




        // return  $formatter;
    }

    /**
     * Parse a string based formatter.
     *
     * @param  string  $formatters
     * @return array
     * @see  Illuminate\Validation\ValidationRuleParser
     */
    protected static function parseStringFormatter($formatters)
    {
        $parameters = [];

        // {method}:{parameters}
        if (strpos($formatters, ':') !== false) {
            list($formatters, $parameter) = explode(':', $formatters, 2);

            $parameters = str_getcsv($parameter);
        }

        return [Str::studly(trim($formatters)), $parameters];
    }
    /**
      * Convert explicit input keys
      * @return $this
      */
    protected function convertExplicitKeys()
    {
        $explicitKeys = collect($this->explicitKeys);

        $explicitKeys->each(function ($methods, $key) {
            dd($this->explodeMethodsToCall(new Conversion), $key, new Conversion instanceof Convertible);
        });

        return $this;
        // foreach ($explictKeys as $input => $formatters) {
        //     $formatters = explode('|', trim($formatters, "|"));

        //     foreach ($formatters as $methods) {
        //         $details = $this->extractIterationDetails($methods);

        //         $params = $details['params'];

        //         $method = $details['method'];

        //         if ($method== 'bailIfEmpty' && $this->bailIfEmpty($request[$inputKey])) {
        //             break;
        //         } elseif ($method== 'bailIfEmpty') {
        //             continue;
        //         }
        //         $data = data_get($request, $input);
        //         if (!is_null($data) && strpos($input, ".")) {
        //             data_set($request, $input, Formatter::call($method, $params, $data)->get());
        //         } elseif (is_array($request[$input])) {
        //             $request[$input] = $this->handleMethodCallsOnArrayInput($request[$input], $method, $params);
        //         } else {
        //             $request[$input] = Formatter::call($method, $params, $request[$input])->get();
        //         }
        //     }
        // }

        // return $request;
    }
    /**
     * Process the formatters and convert the input
     * @param array $request
     * @param array $explicitKeys
     * @param array $pattern
     * @param array $request
     */
    public function process($request, $explicitKeys, $patterns)
    {
        $request = $this->convertExplicitKeys($request, $explicitKeys);

        $request = $this->convertPatternInput($request, $patterns);

        return $request;
    }
    /**
     * Determine if the given value needs to be
     * skipped in mass conversions
     * @param  mixed $value
     * @return bool
     */
    protected function bailIfEmpty($value)
    {
        if (is_array($value) && empty($value)) {
            return true;
        }
        if (is_null($value) || $value == '') {
            return true;
        }

        return false;
    }
    /**
    * Convert pattern input keys
    * @param  array $request
    * @param  array $formatters
    * @return array $request
    */
    private function convertPatternInput($request, $formatters)
    {
        foreach ($formatters as $input => $methods) {
            $matches = array_filter($request, function ($key) use ($input) {
                return Str::is($input, $key);
            }, ARRAY_FILTER_USE_KEY);


            foreach ($matches as $inputKey => $inputValue) {
                $formatters = explode('|', trim($methods, "|"));

                foreach ($formatters as $method) {
                    $details = $this->extractIterationDetails($method);

                    $params = $details['params'];

                    $method = $details['method'];

                    if ($method== 'bailIfEmpty' && $this->bailIfEmpty($request[$inputKey])) {
                        break;
                    } elseif ($method== 'bailIfEmpty') {
                        continue;
                    }

                    if (is_array($request[$inputKey])) {
                        $request[$inputKey] = $this->handleMethodCallsOnArrayInput($request[$inputKey], $method, $params);
                    } else {
                        //always return the value not the class
                        $request[$inputKey] = Formatter::call($method, $params, $request[$inputKey])->get();
                    }
                }
            }
        }

        return $request;
    }

    /**
     * Get the necessary details about the current
     * formatter iteration in our convert methods
     * @param  mixed $iteration
     * @return array
     */
    private function extractIterationDetails($iteration)
    {
        return [
            'params'=>strpos($iteration, ":") ? explode(",", Str::after($iteration, ":")) : [],
            'method'=>Str::before($iteration, ":")
        ];
    }

    /**
    * Explode the explicit formatter into an array if necessary.
    *
    * @param  mixed  $formatter
    * @return array
    */
    protected function explodeFormatter($formatter)
    {
        if (is_string($formatter)) {
            return explode('|', $formatter);
        } elseif (is_object($formatter)) {
            return [$this->getConvertibleObjectValue($formatter)];
        }

        // return array_map([$this, 'getConvertibleObjectValue'], $formatter);
    }
}
