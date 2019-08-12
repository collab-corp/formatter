<?php

namespace CollabCorp\Formatter;

use Closure;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use CollabCorp\Formatter\FormattedData;
use CollabCorp\Formatter\Contracts\Formattable;
use Illuminate\Validation\ValidationRuleParser;

class FormatterRuleParser extends ValidationRuleParser
{
    /**
     * The method name to use for bailing on empty input.
     *
     * @var string
     */
    protected static $bailMethodName = 'bailIfEmpty';

    /**
    * Prepare the given rule for the FormattedData.
    *
    * @param  mixed  $rule
    * @return mixed
    */
    protected function prepareRule($rule)
    {
        if (is_string($rule) || $rule instanceof Formattable || $rule instanceof Closure) {
            return $rule;
        }

        try {
            return (string) $rule;
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                'Invalid rule type encountered. Rule must be Closure, String or Formattable object.'
            );
        }
    }

    /**
     * Process the given rules on the data
     * using the given key.
     *
     * @param  array $data
     * @param  mixed $rules
     * @param  string $key
     * @return array
     */
    public static function processRulesOnKey(array $data, $rules, string $key)
    {
        if (!is_array($rules)) {
            $rules = [$rules];
        }

        foreach ($rules as $rule) {
            $value = Arr::get($data, $key);

            $options = static::parseRule($rule, $value);

            if ($options['bail'] ?? false) {
                break;
            } elseif ($options['method'] == static::$bailMethodName) {
                continue;
            }

            if (!($options['value_replaced'] ?? false) && !static::methodIsCallableOnValue($value, $options['method'])) {
                array_unshift($options['parameters'], $value);
            }

            $newValue = static::applyRule($options, $value);

            Arr::set($data, $key, $newValue);
        }

        return $data;
    }

    /**
     * Check if the given value is an object
     * and the given method exists on it.
     *
     * @param  mixed $value
     * @param  string $method
     * @return boolean
     */
    protected static function methodIsCallableOnValue($value, $method)
    {
        return is_object($value) && method_exists($value, $method);
    }

    /**
     * Check if the given method is whitelisted.
     *
     * @param  string $method
     * @return bool
     */
    protected static function isWhiteListed($method)
    {
        if (in_array($method, FormattedData::getWhiteList())) {
            return true;
        }

        return false;
    }
    /**
     * Apply a rule using the given options and value.
     *
     * @param  array $options
     * @param  $value - the value being formatted.
     * @note $value may already be included in $options['parameters'], it is only provided to allow
     * this function to process underlying objects should $options['method'] not be a callable and
     * value is an object (ie. Carbon.)
     * @return mixed
     */
    protected static function applyRule(array $options, $value)
    {
        $method = $options['method'];
        $parameters = $options['parameters'];

        if (is_callable($method)) {
            if (is_string($method) && !static::isWhiteListed($method)) {
                static::throwInvalidFormatterRule($method);
            }
            return $method(...$parameters);
        } elseif (static::methodIsCallableOnValue($value, $method)) {
            return $value->{$method}(...$parameters);
        }


        static::throwInvalidFormatterRule($method);
    }
    /**
     * Throw an invalid argument exception using the
     * given method. Thrown when
     * @param  string $method
     * @throws InvalidArgumentException
     */
    protected static function throwInvalidFormatterRule($method)
    {
        if (is_array($method)) {
            $error = 'Invalid callable rule encountered.';
        } else {
            $error = sprintf("Non callable rule encountered, [%s].", $method);
        }

        throw new InvalidArgumentException($error);
    }
    /**
     * Check if the given value is empty.
     *
     * @param  mixed $value
     * @return boolean
     */
    protected static function isEmptyValue($value)
    {
        if (is_array($value)) {
            return empty($value);
        }
        return is_null($value) || $value == '';
    }

    /**
     * Parse a single rule and return useful
     * data that is used during rule processing.
     *
     * @param  mixed $rule
     * @param  mixed $inputValue
     * @return array
     */
    protected static function parseRule($rule, $inputValue)
    {
        if (is_string($rule)) {
            return static::parseRuleString($rule, $inputValue);
        } elseif ($rule instanceof Formattable) {
            return [
                'method'=>[$rule, 'format'],
                'parameters'=>[$inputValue]
            ];
        } elseif ($rule instanceof Closure) {
            return [
                'method'=>$rule,
                'parameters'=>[$inputValue]
            ];
        }
    }
    /**
     * Parse a string rule and return options
     * for callables during rule processing.
     * @param  string $rule
     * @param  mixed $inputValue
     * @return array
     */
    protected static function parseRuleString(string $rule, $inputValue)
    {
        if ($rule == static::$bailMethodName) {
            return [
                'method'=>static::$bailMethodName,
                'bail'=>static::isEmptyValue($inputValue)
            ];
        }

        $valueReplaced = null;

        $parameters = [];

        if (strpos($rule, ':') !== false) {
            list($rule, $parameter) = explode(':', $rule);

            /**
             * By default, we assume we are passing parameters in the following order:
             * method(value, ...). But if the developer specifies '$value' in the rule methods
             * then we will pass it in that order instead. This allows us to specify what position
             * the value argument is when passing these parameters to php functions.
             */
            $parameters = array_map(function ($paramValue) use ($inputValue, &$valueReplaced) {
                if ($paramValue == '$value') {
                    $valueReplaced = true;
                    return $inputValue;
                }
                return $paramValue;
            }, str_getcsv($parameter));
        }

        $options = [
            'method'=> trim($rule),
            'parameters'=>$parameters
        ];

        if ($valueReplaced) {
            $options['value_replaced'] = true;
        }

        return $options;
    }
}
