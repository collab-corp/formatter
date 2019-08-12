<?php

namespace CollabCorp\Formatter;

class FormattedData
{
    /**
     * The data being formatted.
     *
     * @var array
     */
    protected $data;

    /**
     * The rules/callables to apply on the data.
     *
     * @var array
     */
    protected $rules;

    /**
     * The allowed callables.
     *
     * @var array
     */
    protected static $whiteList = [];

    /**
     * Construct a new DataFormatter instance.
     *
     * @param mixed $data
     * @param array  $rules
     */
    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Register a whitelist for the allowed callables.
     *
     * @param  array  $whiteList
     */
    public static function registerCallableWhiteList(array $whiteList = [])
    {
        static::$whiteList = $whiteList;
    }


    public static function getWhiteList()
    {
        return static::$whiteList;
    }

    /**
     * Get the formatted data.
     *
     * @return array
     */
    public function get()
    {
        $parsed = (new FormatterRuleParser($this->data))->explode($this->rules);

        foreach ($parsed->rules as $requestKey => $inputRules) {
            if (is_string($inputRules)) {
                $inputRules = explode('|', trim($inputRules, '|'));
            }
            $this->data = FormatterRuleParser::processRulesOnKey($this->data, $inputRules, $requestKey);
        }
        return $this->data;
    }
}
