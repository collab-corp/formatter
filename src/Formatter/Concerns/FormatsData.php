<?php

namespace CollabCorp\Formatter\Concerns;

use CollabCorp\Formatter\FormattedData;

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
        return (new FormattedData($data, $rules))->get();
    }
}
