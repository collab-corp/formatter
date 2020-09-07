<?php

namespace CollabCorp\Formatter\Support\Concerns;

use CollabCorp\Formatter\DataFormatter;

trait FormatsData
{
    /**
     * Format the given data using the given rules.
     *
     * @param  array  $data
     * @param  array  $rules
     * @return array
     */
    public function format(array $data, array $rules)
    {
        return (new DataFormatter($data, $rules))->get();
    }
}
