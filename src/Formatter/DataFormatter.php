<?php

namespace CollabCorp\Formatter;

use Closure;
use CollabCorp\Formatter\Support\Contracts\Formattable;
use CollabCorp\Formatter\Support\RuleParser;
use CollabCorp\Formatter\Support\ValueFormatter;
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

        $this->parser = new RuleParser($this->data);

        $this->formatter = new ValueFormatter();
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
     * Apply the given rules to the given data key.
     *
     * @param  string
     * @param  array  $rules
     * @return self
     */
    public function applyRules(string $key, array $rules)
    {
        if (!Arr::has($this->data, $key)) {
            return $this;
        }

        $value = Arr::get($this->data, $key);

        $this->formatter->setValue($value);

        $this->formatter->setRules($rules);

        $this->formatter->allowedCallables($this->allowedCallables);

        Arr::set($this->data, $key, $this->formatter->apply()->get());

        return $this;
    }

    /**
     * Get the formatted data.
     *
     * @return array
     */
    public function get()
    {
        $parsed = $this->parser->explode($this->rules);

        foreach ($parsed->rules as $key => $rules) {
            $this->applyRules($key, $rules);
        }

        return $this->data;
    }
}
