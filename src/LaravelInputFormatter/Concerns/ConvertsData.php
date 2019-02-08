<?php

namespace CollabCorp\LaravelInputFormatter\Concerns;

use CollabCorp\LaravelInputFormatter\DataFormatter;
use Illuminate\Http\Request;

trait ConvertsData
{
    /**
     * Format the given input using the specified formatters.
     * @param  mixed $request
     * @param  array $formatters
     * @param  bool $replaceRequestData
     * @return Illuminate\Support\Collection
     */
    protected function convert($request, array $formatters=[], bool $replaceRequestData = true)
    {
        $data = DataFormatter::convert($formatters, $input);

        if ($request instanceof Request) {
            //if we're working with a request object, automatically replace data if specified.
            if ($replaceRequestData) {
                $request->replace($data->all());
            }
        }

        return $data;
    }
}
