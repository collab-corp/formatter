<?php

namespace CollabCorp\Formatter\Support;

use Closure;
use CollabCorp\Formatter\Support\Contracts\Formattable;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationRuleParser;

class RuleParser extends ValidationRuleParser
{
    /**
     * Parse an array based rule.
     *
     * @param  array  $rules
     * @return array
     */
    protected static function parseArrayRule(array $rules)
    {
        return [trim(Arr::get($rules, 0)), array_slice($rules, 1)];
    }

    /**
     * Parse a string based rule.
     *
     * @param  string  $rules
     * @return array
     */
    protected static function parseStringRule($rules)
    {
        $parameters = [];

        if (strpos($rules, ':') !== false) {
            [$rules, $parameter] = explode(':', $rules, 2);

            $parameters = static::parseParameters($rules, $parameter);
        }

        return [trim($rules), $parameters];
    }

    /**
    * Prepare the given rule for parsing.
    *
    * @param  mixed  $rule
    * @return mixed
    */
    protected function prepareRule($rule)
    {
        if (is_string($rule) || $rule instanceof Formattable || $rule instanceof Closure) {
            return $rule;
        }

        return parent::prepareRule($rule);
    }

    /**
    * Extract the rule name and parameters from a rule.
    *
    * @param  array|string  $rules
    * @return array
    */
    public static function parse($rules)
    {
        if ($rules instanceof Formattable || $rules instanceof Closure) {
            return [$rules, []];
        }

        return parent::parse($rules);
    }
}
