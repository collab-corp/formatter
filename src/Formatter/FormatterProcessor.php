<?php

namespace CollabCorp\Formatter;

use CollabCorp\Formatter\Formatter;
use Illuminate\Support\Str;

class FormatterProcessor
{

     /**
     * Whitelist of the allowed methods to be called via the traits on formatter class
     * @var Array $whiteList
     */
    protected $whiteList =[
        //String methods
        'after',
        'bcrypt',
        'before',
        'camelCase',
        'decrypt',
        'encrypt',
        'finish',
        'insertEvery',
        'kebabCase',
        'phone',
        'limit',
        'ltrim',
        'onlyAlphaNumeric',
        'onlyNumbers',
        'onlyLetters',
        'plural',
        'prefix',
        'rtrim',
        'slug',
        'snakeCase',
        'ssn',
        'start',
        'studlyCase',
        'suffix',
        'titleCase',
        'trim',
        'truncate',
        'url',
        //Math methods
        'decimals',
        'add',
        'subtract',
        'multiply',
        'divide',
        'power',
        'percentage',
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

    protected $value;

    /**
     * Process the formatters and convert the input
     * @param  Array $requestInput
     * @param Illuminate\Database\Eloquent\Collection $explicitKeys
     * @param Illuminate\Database\Eloquent\Collection $startsWith
     * @param Illuminate\Database\Eloquent\Collection $endWith
     * @param Illuminate\Database\Eloquent\Collection $contains
     * @param Array $requestInput
     */
    public function process($requestInput, $explictKeys, $startsWith, $endsWith, $contains)
    {
        $request = $requestInput->all();

        $request = $this->convertExplicitKeys($request, $explictKeys);

        $request = $this->convertPatternInput($request, $startsWith);

        $request = $this->convertPatternInput($request, $endsWith);


        $request = $this->convertPatternInput($request, $contains);


        return $request;
    }



    /**
     * Convert pattern input keys
     * @param  Array|Illuminate\Support\Collection $request
     * @param  Illuminate\Database\Eloquent\Collection $collection
     * @return Array $request
     */
    private function convertPatternInput($request, $collection)
    {
        if (!($request instanceof Illuminate\Support\Collection)) {
            $request = collect($request);
        }
        $matches = collect($request);

        foreach ($collection->all() as $input => $formattersToProcess) {
            $matches = $request->filter(function ($value, $key) use ($input) {
                return Str::is($input, $key);
            });

            foreach ($matches->all() as $inputKey => $inputValue) {
                $formatters = explode('|', $formattersToProcess);

                foreach ($formatters as $formatterMethods) {
                    $formatterMethods = trim($formatterMethods, "|");

                    $params = strpos($formatterMethods, ":") ? explode(",", str_after($formatterMethods, ":")) : [];

                    $method = str_before($formatterMethods, ":");

                    if (is_array($request[$inputKey])) {
                        $request[$inputKey] = $this->handleArrayInput($request[$inputKey], $method, $params);
                    } else {
                        $request[$inputKey] = $this->callMethod($method, $params, $request[$inputKey]);
                    }
                }
            }
        }

        return $request;
    }
    /**
     * Convert explicit input keys
     * @param  Array $request
     * @param  Illuminate\Database\Eloquent\Collection $explicitKeys
     * @return Array $requestInput
     */
    private function convertExplicitKeys($request, $explictKeys)
    {
        foreach ($explictKeys->all() as $input => $formatters) {
            $formatters = explode('|', $formatters);

            foreach ($formatters as $formatterMethods) {
                $formatterMethods = trim($formatterMethods, "|");

                $params = strpos($formatterMethods, ":") ? explode(",", str_after($formatterMethods, ":")) : [];

                $method = str_before($formatterMethods, ":");

                if (is_array($request[$input])) {
                    $request[$input] = $this->handleArrayInput($request[$input], $method, $params);
                } else {
                    $request[$input] = $this->callMethod($method, $params, $request[$input]);
                }
            }
        }

        return $request;
    }
    /**
     * Handle an array input field
     * @param  Array $input
     * @param  String $method
     * @param  Array $params
     * @return Array $newValues
     */
    private function handleArrayInput($input, $method, $params)
    {
        $newValues = [];

        foreach ($input as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    $newValues[$key][$nestedKey] = $this->callMethod($method, $params, $nestedValue);
                }
            } else {
                $newValues[$key] = $this->callMethod($method, $params, $value);
            }
        }

        return $newValues;
    }


    /**
     * Call the current method during our process method
     * @param  String $method
     * @param  Array $params
     * @param  mixed $value
     * @return mixed $newValue
     */
    public function callMethod($method, $params, $value)
    {
        if (method_exists(Formatter::class, Str::camel($method))) {
            if (!in_array($method, $this->whiteList)) {
                throw new \Exception("CollabCorp\Formatter\Formatter: Call to undefined formatter method $method.");
            }
        } else {
            throw new \Exception("CollabCorp\Formatter\Formatter: Call to undefined formatter method $method.");
        }

        $formatter = Formatter::singleton($value);

        $formatter = call_user_func_array(array($formatter, $method), (array)$params);

        $newValue = $formatter->get();

        return $newValue;
    }
}
