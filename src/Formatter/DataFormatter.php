<?php

namespace CollabCorp\Formatter;

use CollabCorp\Formatter\Support\CallableParser;
use CollabCorp\Formatter\Support\ValueFormatter;
use Illuminate\Support\Arr;

class DataFormatter
{
    /**
     * The data being formatted.
     *
     * @var array
     */
    protected array $data;

    /**
     * The callable rules to apply on the data.
     *
     * @var array
     */
    protected array $callables;

    /**
     * The allowed callables.
     *
     * @var array
     */
    protected array $allowedCallables = ['*'];

    /**
     * Construct a new instance.
     *
     * @param array $data
     * @param array  $callables
     */
    public function __construct(array $data, array $callables)
    {
        $this->data = $data;

        $this->callables = $callables;

        $this->parser = new CallableParser($this->data);

        $this->formatter = new ValueFormatter();
    }

    /**
     * Create an instance.
     *
     * @param  array $data
     * @param  array $callables
     * @return CollabCorp\Formatter\DataFormatter
     */
    public static function create($data, $callables)
    {
        return new static($data, $callables);
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
     *
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
     * @param  array  $callables
     * @return self
     */
    public function applyRules(string $key, array $callables)
    {
        if (!Arr::has($this->data, $key)) {
            return $this;
        }

        $value = Arr::get($this->data, $key);

        $this->formatter->setValue($value);

        $this->formatter->setCallables($callables);

        $this->formatter->allowedCallables($this->allowedCallables);

        Arr::set($this->data, $key, $this->formatter->apply()->get());

        return $this;
    }

    /**
     * Apply the callables.
     *
     * @return self
     */
    public function apply()
    {
        $parsed = $this->parser->explode($this->callables);

        foreach ($parsed->rules as $key => $callables) {
            $this->applyRules($key, $callables);
        }

        return $this;
    }

    /**
     * Get the formatted data.
     *
     * @return array
     */
    public function get()
    {
        return $this->data;
    }
}
