<?php

namespace CollabCorp\Formatter\Converters;

use CollabCorp\Formatter\Formatter;
use Illuminate\Support\Str;

class StringConverter extends Formatter
{
    /**
    * Whitelist of the allowed methods to be called on this class.
    * @var array
    */
    protected $whiteList =[
        'after',
        'bcrypt',
        'before',
        'camelCase',
        'decrypt',
        'encrypt',
        'explode',
        'finish',
        'insertEvery',
        'kebabCase',
        'phone',
        'limit',
        'ltrim',
        'onlyAlphaNumeric',
        'onlyNumbers',
        'onlyLetters',
        'plural',
        'prefix',
        'replace',
        'rtrim',
        'singleSpaceBetweenWords',
        'slug',
        'snakeCase',
        'ssn',
        'start',
        'studlyCase',
        'suffix',
        'titleCase',
        'trim',
        'truncate',
        'toUpper',
        'toBool',
        'toLower',
        'url'
    ];
    /**
     * Explode the string into an array
     * using the given delimiter.
     * @param  string $delimiter
     * @return static
     */
    public function explode($delimiter = ",")
    {
        $this->value = explode($delimiter, $this->value);

        return $this;
    }

    /**
     * Replace all spacing between words
     * to one single space.
     * @return static
     */
    public function singleSpaceBetweenWords()
    {
        $this->value = preg_replace('/\s+/', ' ', trim($this->value));

        return $this;
    }
    /**
     * Convert the string value
     * to a boolean value.
     * @return static
     */
    protected function toBool()
    {
        $isString = is_string($this->value);

        $value = $isString ? strtolower($this->value) : $this->value;

        if ($value === 'true' || $value === '1') {
            $this->value = true;
        } elseif ($value === 'false' || $value === '0') {
            $this->value = false;
        } elseif ($isString) {
            $this->value = filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
        }

        return $this;
    }
    /**
     * Change a 9 numeric value to a social security format i.e. xxx-xx-xxxx
     * @return static
     */
    public function ssn()
    {
        if (mb_strlen($this->value) == '9' && is_numeric($this->value)) {
            $this->value = substr($this->value, 0, 3).'-'.substr($this->value, 3, 2).'-'.substr($this->value, 5, mb_strlen($this->value));
        }
        return $this;
    }
    /**
     * Insert a given character after every nth character
     * till we hit the end of the value.
     * @param  integer $nth
     * @param  string $insert
     * @return static
     */
    public function insertEvery($nth, $insert)
    {
        $this->value = rtrim(chunk_split($this->value, $nth, $insert), $insert);
        return $this;
    }
    /**
     * Truncate off the specifed number of characters
     * @param  $takeOff
     * @return static
     */
    public function truncate($takeOff = 0)
    {
        $this->value = rtrim($this->value, substr($this->value, mb_strlen($this->value) -($takeOff)));
        return $this;
    }
    /**
     * Add the given value to our value if it does not already end with the value
     * @param string $finish
     * @return static
     */
    public function finish($finish)
    {
        $this->value = Str::finish($this->value, $finish);
        return $this;
    }
    /**
     * Add the given value to our value if it does not already start with the value
     * @param string $start
     * @return static
     */
    public function start($start)
    {
        $this->value = Str::start($this->value, $start);
        return $this;
    }
    /**
     * Get everything before the specified value
     * @param string $before
     * @return static
     */
    public function before($before)
    {
        $this->value = Str::before($this->value, $before);
        return $this;
    }
    /**
     * Get everything after the specified value
     * @param string $before
     * @return static
     */
    public function after($after)
    {
        $this->value = Str::after($this->value, $after);
        return $this;
    }
    /**
     * Prefix something onto our value
     * @param string $prefix
     * @return static
     */
    public function prefix($prefix)
    {
        $this->value = $prefix.$this->value;
        return $this;
    }
    /**
     * Add a suffix onto our value
     * @param string $suffix
     * @return static
     */
    public function suffix($suffix)
    {
        $this->value = $this->value.$suffix;
        return $this;
    }

    /**
     * Convert the value to camel case.
     * @return static
     */
    public function camelCase()
    {
        $this->value = Str::camel($this->value);

        return $this;
    }
    /**
     * Convert the value to a slug friendly string.
     * @return static
     */
    public function slug()
    {
        $this->value = Str::slug($this->value);

        return $this;
    }
    /**
     * Formatter the value to kebab case.
     * @return static
     */
    public function kebabCase()
    {
        $this->value = Str::kebab($this->value);

        return $this;
    }
    /**
     * Formatter the value to snake case.
     * @return static
     */
    public function snakeCase()
    {
        $this->value = Str::snake($this->value);

        return $this;
    }

    /**
     * Formatter the value to title case.
     * @return static
     */
    public function titleCase()
    {
        $this->value = Str::title($this->value);

        return $this;
    }
    /**
     * Formatter the value to studly case.
     * @return static
     */
    public function studlyCase()
    {
        $this->value = Str::studly($this->value);

        return $this;
    }

    /**
     * Formatter the value to its plural form.
     * @return static
     */
    public function plural()
    {
        $this->value = Str::plural($this->value);

        return $this;
    }

    /**
     * Convert the string to "pretty/label"
     * format. eg. some_column_name-> Some Column Name
     * @return static
     */
    public static function label()
    {
        $this->value = preg_replace("/[^A-Za-z0-9]/", " ", $this->value);
        $this->value = Str::title($this->value);
        return $this;
    }
    /**
     * Formatter the value to a pretty phone format (xxx) xxx-xxxx
     * @return static
     */
    public function phone()
    {
        if (mb_strlen($this->value) == '11' && is_numeric($this->value)) {
            $this->value = substr($this->value, 0, 1).'('.substr($this->value, 1, 3).')'.substr($this->value, 4, 3).'-'.substr($this->value, 7);
        } elseif (mb_strlen($this->value) == '10' && is_numeric($this->value)) {
            $this->value = '('.substr($this->value, 0, 3).')'.substr($this->value, 3, 3).'-'.substr($this->value, 6);
        }
        return $this;
    }
    /**
     * Encrypt our value
     * @return static
     */
    public function encrypt()
    {
        $this->value = encrypt($this->value);

        return $this;
    }

    /**
     * Decrypt our value
     * @return static
     */
    public function decrypt()
    {
        $this->value = decrypt($this->value);
        return $this;
    }
    /**
     * Hash our value with bcrypt
     * @return static
     */
    public function bcrypt()
    {
        $this->value = bcrypt($this->value);
        return $this;
    }

    /**
     * Limit the string by number of specified characters
     * and append a string
     * @param  string $limit
     * @param  string $append
     * @return static
     */
    public function limit($limit = null, $append = '')
    {
        if ($limit === null) {
            $limit = mb_strlen($this->value);
        }
        $this->value = Str::limit($this->value, $limit, $append);
        return $this;
    }

    /**
     * Convert the string to all lower case letters.
     * @return static
     */
    public function toLower()
    {
        $this->value = mb_strtolower($this->value);

        return $this;
    }
    /**
    * Convert the string to all lower case letters.
    * @return static
    */
    public function toUpper()
    {
        $this->value = mb_strtoupper($this->value);

        return $this;
    }
    /**
     * Replace all the given characters with the given character
     * @param  string $search
     * @param  string $replace
     * @return static
     */
    public function replace($search, $replace = '')
    {
        $this->value = str_replace($search, $replace, $this->value);

        return $this;
    }
    /**
     * Remove everything but numbers from the value
     * @return static
     */
    public function onlyNumbers()
    {
        $this->value = preg_replace('/[^0-9]/', '', $this->value);

        return $this;
    }
    /**
     * Convert the value to a url using laravels url helper
     * @return static
     */
    public function url()
    {
        $this->value = url($this->value);
        return $this;
    }

    /**
     * Remove everything but letters from the value
     * @return static
     */
    public function onlyLetters()
    {
        $this->value = preg_replace('/\PL/u', '', $this->value);

        return $this;
    }

    /**
     * Remove all non alpha numeric characters
     * including spaces, unless specified.
     * @param  boolean $allowSpaces
     * @return static
     */
    public function onlyAlphaNumeric($allowSpaces = false)
    {
        $regex = $allowSpaces==true ? "/[^0-9a-zA-Z ]/":"/[^0-9a-zA-Z]/";

        $this->value = preg_replace($regex, "", $this->value);

        return $this;
    }

    /**
     * Remove Leading and ending characters
     * @param  string $trimOff
     * @return static
     */
    public function trim($trimOff = ' ')
    {
        $this->value = trim($this->value, $trimOff);

        return $this;
    }
    /**
     * Remove leading characters
     * @param  string $trimOff
     * @return static
     */
    public function ltrim($trimOff = ' ')
    {
        $this->value = ltrim($this->value, $trimOff);

        return $this;
    }
    /**
     * Remove ending characters
     * @param  string $trimOff
     * @return static
     */
    public function rtrim($trimOff = ' ')
    {
        $this->value = rtrim($this->value, $trimOff);

        return $this;
    }
}
