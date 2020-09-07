<?php

namespace CollabCorp\Formatter;

use CollabCorp\Formatter\Support\RuleParser;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class DataFormatter
{
    /**
     * The data being formatted.
     *
     * @var array
     */
    protected $data;

    /**
     * The callable rules to apply on the data.
     *
     * @var array
     */
    protected $rules;

    /**
     * The allowed callables.
     *
     * @var array
     */
    protected static $allowedCallables = ['*'];

    /**
     * Construct a new instance.
     *
     * @param array $data
     * @param array  $rules
     */
    public function __construct(array $data, array $rules)
    {
        $this->data = $data;

        $this->rules = $rules;
    }

    /**
     * Create an instance.
     * @param  array $data
     * @param  array $rules
     * @return self
     */
    public static function create($data, $rules)
    {
        return new static($data, $rules);
    }

    /**
     * Check if the given method is whitelisted
     * and is allowed to be called during formatting.
     *
     * @param  string $method
     * @return bool
     */
    protected static function callableIsAllowed($method)
    {
        //todo check Formattable class/instance.
        if (!is_callable($method)) {
            return false;
        }

        if (
            static::$allowedCallables == ['*'] ||
            in_array($method, static::$allowedCallables)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Register a whitelist for the allowed callables.
     *
     * @param  array  $whitelist
     */
    public static function allowedCallables(array $whitelist = [])
    {
        static::$allowedCallables = $whitelist;
    }

    /**
     * Get the registered allowed callables.
     * @return array
     */
    public static function getAllowedCallables()
    {
        return static::$allowedCallables;
    }
    /**
     * Prepare arguments for a rule function call.
     * @param  mixed  $value
     * @param  array  $args
     * @return array
     */
    protected function prepareArguments($value, array $args = [])
    {
        $parameters = [$value];

        $parameters = array_map(function ($arg) use (&$parameters, $value) {
            if ($arg === ':value:') {
                $arg = $value;
                array_pop($parameters);
            }
            return $arg;
        }, $args);

        return $parameters;
    }

    /**
     * Call a given callable using the
     * given value and parameters.
     * @param  string $value
     * @return mixed
     */
    protected function call($callable, $value, array $args = [])
    {
        if (!static::callableIsAllowed($callable)) {
            throw new InvalidArgumentException(
                sprintf('Encountered invalid callable [%s].', $callable)
            );
        }

        $args = $this->prepareArguments($value, $args);

        return $callable(...$args);
    }
    /**
     * Get the formatted data.
     *
     * @return array
     */
    public function get()
    {
        $parser = new RuleParser($this->data);

        $parsed = $parser->explode($this->rules);

        foreach ($parsed->rules as $key => $rules) {
            if (is_string($rules)) {
                $rules = explode('|', trim($rules, '|'));
            }

            foreach ($rules as $rule) {
                list($rule, $parameters) = $parser->parse($rule);

                //todo wildcard???
                $result = $this->call(
                    $rule,
                    Arr::get($this->data, $key),
                    $parameters
                );

                Arr::set($this->data, $key, $result);
            }

            // $this->data = FormatterRuleParser::processRulesOnKey($this->data, $inputRules, $requestKey);
        }

        return $this->data;
    }
}
