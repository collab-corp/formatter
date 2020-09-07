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
    protected $allowedCallables = ['*'];

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
     * @return CollabCorp\Formatter\DataFormatter
     */
    public static function create($data, $rules)
    {
        return new static($data, $rules);
    }
    /**
     * Register a whitelist for the allowed callables.
     *
     * @param  array  $whitelist
     */
    public function allowedCallables(array $whitelist = [])
    {
        $this->allowedCallables = $whitelist;
    }

    /**
     * Get the registered allowed callables.
     * @return array
     */
    public function getAllowedCallables()
    {
        return $this->allowedCallables;
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

        return empty($parameters) ? [$value] : $parameters;
    }

    /**
     * Check if the given method that should
     * be called on the underlying object.
     * @param  string $method
     * @return bool
     */
    protected function callableIsUnderlyingCall(string $method)
    {
        return substr($method, 0, 1) == '.';
    }
    /**
     * Call a given callable using the
     * given value and parameters.
     * @param  string $value
     * @return mixed
     */
    protected function call($callable, $value, array $args = [])
    {
        $args = $this->prepareArguments($value, $args);

        // first check if the method should be called on the underlying object
        // this is done using a .<method> convention. e.g to_carbon|.format:m/d/Y
        if ($isUnderlyingCall = $this->callableIsUnderlyingCall($callable)) {
            $callable = trim($callable, '.');
        }
        //if it is, call it.
        if ($isUnderlyingCall) {
            return $value->{$callable}(...$args);
        //otherwise check if the callable is callable and whitelisted.
        } elseif (!is_callable($callable) || !$this->isWhitelisted($callable)) {
            throw new InvalidArgumentException(
                sprintf('Encountered non callable [%s].', $callable)
            );
        }

        return $callable(...$args);
    }
    /**
     * Check if the given method is whitelisted.
     *
     * @param  string $method
     * @return bool
     */
    protected function isWhitelisted(string $method)
    {
        return $this->allowedCallables == ['*'] ||
            in_array($method, $this->allowedCallables);
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
