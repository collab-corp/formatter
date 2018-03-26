<?php
namespace CollabCorp\Formatter\Exceptions;

class FormatterException extends \RuntimeException
{
    public static function notFound($formatter)
    {
        return new static("No formatter implements [{$formatter}].");
    }
}
