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
    /**
     * Throw an exception when attempting
     * to cast a formatter object with multiple
     * values.
     * @return CollabCorp\Formatter\Exceptions\FormatterException
     */
    public static function stringCastOnMultipleValues()
    {
        return new static("Cannot cast formatter as string when formatter has multiple values");
    }
}
