<?php

namespace CollabCorp\Formatter\Concerns;

use CollabCorp\Formatter\Formatter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ConvertsInput
{
    /**
     * Format request input using
     * the given formatters.
     * @param  mixed $request
     * @param  array $formatters
     * @return Illuminate\Support\Collection
     */
    protected function convert($request, array $formatters=[])
    {
        if (($isRequest = $request instanceof Request)|| $request instanceof Collection) {
            $input = $request->all();
        } elseif ($request instanceof Arrayable) {
            $input = $request->toArray();
        }

        $data = Formatter::convert($formatters, $input);

        //if we're working with a equest object, automatically replace data
        if ($isRequest) {
            $request->replace($data->all());
        }

        return $data;
    }
}
