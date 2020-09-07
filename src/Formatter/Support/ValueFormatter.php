<?php
namespace CollabCorp\Formatter\Support;

use Closure;
use CollabCorp\Formatter\Support\Contracts\Formattable;
use CollabCorp\Formatter\Support\RuleParser;
use Illuminate\Support\Str;
use RuntimeException;

class ValueFormatter
{
    /**
     * The value being formatted.
     * @var mixed
     */
    protected $value;

    /**
     * The rules to apply to the value.
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
     * @param mixed $value
     * @param array  $rules
     */
    public function __construct($value = '', array $rules = [])
    {
        $this->value = $value;

        $this->rules = $rules;
    }

    /**
     * Set the value.
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Set the rules.
     * @param mixed $rules
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }
    /**
     * Get the value.
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Prepare arguments for a rule function call.
     *
     * @param  mixed  $value
     * @param  array  $args
     * @return array
     */
    protected function prepareArguments($value, $callable, array $args = [])
    {
        //underlying function calls do not get the value passed in by default
        //since the value is the function being called, value is not needed as a param.
        if (is_object($value) && $this->callableIsUnderlyingCall($callable)) {
            $defaults = [];
        } else {
            $defaults = [$value];
        }

        $parameters = array_merge($defaults, $args);

        foreach ($parameters as $index => $param) {
            if ($param === ':value:') {
                $parameters[$index] = $value;
                array_shift($parameters);
                break;
            }
        }
        return $parameters;
    }

    /**
     * Call callable using the given value and parameters.
     *
     * @param  string $value
     * @return mixed
     */
    protected function call($callable, $value, array $args = [])
    {
        //first prepare the arguments for the function call.
        $args = $this->prepareArguments($value, $callable, $args);

        // check if the method should be called on an underlying object
        // this is done using a .<method> convention.
        // this allows for formatting to be delegated to 3rd party classes such
        // as carbon. e.g to_carbon|.format:m/d/Y
        if (is_object($value) && $this->callableIsUnderlyingCall($callable)) {
            $callable = ltrim($callable, '.');
            return $value->{$callable}(...$args);
        }

        //check if its a custom formattable class
        if ($callable instanceof Formattable) {
            return $callable->format($value);
        }
        //otherwise check if the method is callable and whitelisted.
        if (is_callable($callable) && $this->isWhitelisted($callable)) {
            return $callable(...$args);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Encountered non whitelisted or non callable [%s].',
                $callable
            )
        );
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
     * Check if the given method is whitelisted.
     *
     * @param  mixed $method
     * @return bool
     */
    protected function isWhitelisted($method)
    {
        if ($method instanceof Formattable || $method instanceof Closure) {
            return true;
        }

        return $this->allowedCallables == ['*'] ||
            in_array($method, $this->allowedCallables);
    }

    /**
     * Check if the given method name
     * indicates it is a underlying
     * object method call.
     *
     * @param  mixed $method
     * @return bool
     */
    protected function callableIsUnderlyingCall($method)
    {
        if (!is_string($method)) {
            return false;
        }

        return substr($method, 0, 1) == '.';
    }

    /**
     * Apply the set rules on the value.
     *
     * @return self
     */
    public function apply()
    {
        $optional = $this->rules[0] ?? '';

        //if the first rule is ? then check if
        //the value is blank and if it should be processed.
        if ($optional == '?') {
            if (blank($this->value)) {
                return $this;
            } else {
                array_shift($this->rules);
            }
        }

        foreach ($this->rules as $rule) {
            list($rule, $parameters) = RuleParser::parse($rule);

            $this->value = $this->call($rule, $this->value, $parameters);
        }

        return $this;
    }
}
