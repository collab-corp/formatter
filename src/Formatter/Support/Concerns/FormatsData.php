<?php

namespace CollabCorp\Formatter\Support\Concerns;

use CollabCorp\Formatter\DataFormatter;
use CollabCorp\Formatter\Support\ValueFormatter;

trait FormatsData
{
    /**
     * Format the given data using the given callables.
     *
     * @param  array $data
     * @param  string|array $callables
     * @return CollabCorp\Formatter\DataFormatter
     */
    public function formatData(array $data, array $callables): DataFormatter
    {
        return (new DataFormatter($data, $callables));
    }
    /**
     * Format the given value given callables.
     *
     * @param  mixed $value
     * @param  array $callables
     * @return ValueFormatter
     */
    public function formatValue($value, array $callables): ValueFormatter
    {
        return (new ValueFormatter($value, $callables));
    }
}
