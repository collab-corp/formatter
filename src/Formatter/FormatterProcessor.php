<?php

namespace CollabCorp\Formatter;

use CollabCorp\Formatter\Concerns\ProcessesMethodCallsOnArrays;
use CollabCorp\Formatter\Formatter;
use Illuminate\Support\Str;

class FormatterProcessor
{
    use ProcessesMethodCallsOnArrays;
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
      * Convert explicit input keys
      * @param  array $request
      * @param  array $explicitKeys
      * @return array $request
      */
    private function convertExplicitKeys($request, $explictKeys)
    {
        foreach ($explictKeys as $input => $formatters) {
            $formatters = explode('|', trim($formatters, "|"));

            foreach ($formatters as $methods) {
                $details = $this->extractIterationDetails($methods);

                $params = $details['params'];

                $method = $details['method'];

                $data = data_get($request, $input);
                if (!is_null($data) && strpos($input, ".")) {
                    data_set($request, $input, Formatter::call($method, $params, $data)->get());
                } elseif (is_array($request[$input])) {
                    $request[$input] = $this->handleMethodCallsOnArrayInput($request[$input], $method, $params);
                } else {
                    $request[$input] = Formatter::call($method, $params, $request[$input])->get();
                }
            }
        }

        return $request;
    }
}
