<?php

namespace CollabCorp\Formatter;

use CollabCorp\Formatter\Formatter;
use Illuminate\Support\Str;

class FormatterProcessor
{


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
        foreach ($formatters as $input => $formattersToProcess) {
            $matches = array_filter($request, function ($key) use ($input) {
                return Str::is($input, $key);
            }, ARRAY_FILTER_USE_KEY);

            foreach ($matches as $inputKey => $inputValue) {
                $formatters = explode('|', $formattersToProcess);

                foreach ($formatters as $formatterMethods) {

                    $details = $this->extractFormatterDetails($formatterMethods);

                    $params = $details['params'];

                    $method = $details['method'];
                    if (is_array($request[$inputKey])) {
                        $request[$inputKey] = $this->handleArrayInput($request[$inputKey], $method, $params);
                    } else {

                        $request[$inputKey] = Formatter::call($method, $params, $request[$inputKey]);
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
    private function extractFormatterDetails($iteration)
    {

        $formatterMethods = trim($iteration, "|");
        return [
            'params'=>strpos($formatterMethods, ":") ? explode(",", str_after($formatterMethods, ":")) : [],
            'method'=>str_before($formatterMethods, ":")
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
            $formatters = explode('|', $formatters);

            foreach ($formatters as $formatterMethods) {
                $details = $this->extractFormatterDetails($formatterMethods);

                $params = $details['params'];

                $method = $details['method'];
                if (!is_null(data_get($request, $input)) && strpos($input, ".")) {
                    data_set($request, $input, Formatter::call($method, $params, data_get($request, $input)));
                } elseif (is_array($request[$input])) {
                    $request[$input] = $this->handleArrayInput($request[$input], $method, $params);
                } else {
                    $request[$input] = Formatter::call($method, $params, $request[$input]);
                }
            }
        }

        return $request;
    }
    /**
     * Handle an array input field
     * @param  array $input
     * @param  String $method
     * @param  array $params
     * @return array $newValues
     */
    private function handleArrayInput($input, $method, $params)
    {
        $newValues = [];


        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $newValues[$key] = $this->handleArrayInput($value, $method, $params);
            } else {
                $newValues[$key] =  Formatter::call($method, $params, $value);
            }
        }

        return $newValues;
    }



}
