<?php

namespace CollabCorp\Formatter\Converters;

use CollabCorp\Formatter\Formatter;

class DataFormatConverter extends Formatter
{
    /**
    * Whitelist of the allowed methods to be called on this class.
    * @var Array $whiteList
    */
    protected $whiteList =[

        'jsonEncode',
        'jsonDecode',
    ];
    /**
     * Encode the value as json string
     * @param int $options
     * @param int $depth
     * @return this
     */
    public function jsonEncode($options = 0, $depth=512)
    {
        if (is_array($this->value)) {
            $this->value = json_encode($this->value, $options);
        }

        return $this;
    }
    /**
     * Encode the value as json string
     * @param int $options
     * @param bool $assoc
     * @param int $depth
     * @return this
     */
    public function jsonDecode($options = 0, $assoc=true, $depth=512)
    {
        if (is_string($this->value)) {
            $this->value = json_decode($this->value, $assoc, $depth, $options);
        }

        return $this;
    }
}
