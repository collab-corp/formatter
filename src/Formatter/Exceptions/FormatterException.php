<?php
namespace CollabCorp\Formatter\Exceptions;

class FormatterException extends \RuntimeException
{
    /**
     * Throw a formatter not found exception.
     * @param  string $formatter
     * @return CollabCorp\Formatter\Exceptions\FormatterException
     */
    public static function notFound($formatter)
    {
        return new static("No formatter implements [{$formatter}].");
    }
}
