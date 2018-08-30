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
     * @param  bool $replaceRequestData
     * @return Illuminate\Support\Collection
     */
    protected function convert($request, array $formatters=[], bool $replaceRequestData = true)
    {
        if (($isRequest = $request instanceof Request)|| $request instanceof Collection) {
            $input = $request->all();
        } elseif ($request instanceof Arrayable) {
            $input = $request->toArray();
        }

        $data = Formatter::convert($formatters, $input);

        if ($isRequest) {
            //if we're working with a request object, automatically replace data if specified.
            if ($replaceRequestData) {
                $request->replace($data->all());
            }
        }

        return $data;
    }
}
