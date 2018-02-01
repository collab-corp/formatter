<?php

namespace CollabCorp\Formatter\Traits;

use Illuminate\Support\Str;

trait HandlesStringConversions
{
    /**
     * Change a 9 numeric value to a social security format i.e. xxx-xx-xxxx
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function ssn()
    {
        if (strlen($this->onlyNumbers($this->value)->get()) == '9') {
            $this->value = $this->onlyNumbers($this->value)->get();
            $this->value = substr($this->value, 0, 3).'-'.substr($this->value, 3, 2).'-'.substr($this->value, 5, strlen($this->value));
        }
        return $this;
    }
    /**
     * Insert a given character after every nth character.
     * @param  integer $nth
     * @param  string $insert
     * @return CollabCorp\Formatter\Formatter
     */
    public function insertEvery($nth, $insert)
    {
        $this->value = chunk_split($this->value, $nth, $insert);
        return $this;
    }
    /**
     * Truncate off the specifed number of characters
     * @param  $takeOff
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function truncate($takeOff = 0)
    {
        $this->value = rtrim($this->value, substr($this->value, strlen($this->value) -($takeOff)));
        return $this;
    }
    /**
     * Add the given value to our value if it does not already end with the value
     * @param String $finish
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function finish($finish)
    {
        $this->value = Str::finish($this->value, $finish);
        return $this;
    }
    /**
     * Add the given value to our value if it does not already start with the value
     * @param String $start
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function start($start)
    {
        $this->value = Str::start($this->value, $start);
        return $this;
    }
    /**
     * Get everything before the specified value
     * @param String $before
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function before($before)
    {
        $this->value = Str::before($this->value, $before);
        return $this;
    }
    /**
     * Get everything after the specified value
     * @param String $before
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function after($after)
    {
        $this->value = Str::after($this->value, $after);
        return $this;
    }
    /**
     * Prefix something onto our value
     * @param String $prefix
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function prefix($prefix)
    {
        $this->value = $prefix.$this->value;
        return $this;
    }
    /**
     * Add a suffix onto our value
     * @param String $suffix
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function suffix($suffix)
    {
        $this->value = $this->value.$suffix;
        return $this;
    }

    /**
     * Convert the value to camel case.
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function camelCase()
    {
        $this->value = Str::camel($this->value);

        return $this;
    }
    /**
     * Convert the value to a slug friendly string.
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function slug()
    {
        $this->value = Str::slug($this->value);

        return $this;
    }
    /**
     * Formatter the value to kebab case.
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function kebabCase()
    {
        $this->value = Str::kebab($this->value);

        return $this;
    }
    /**
     * Formatter the value to snake case.
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function snakeCase()
    {
        $this->value = Str::snake($this->value);

        return $this;
    }

    /**
     * Formatter the value to title case.
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function titleCase()
    {
        $this->value = Str::title($this->value);

        return $this;
    }
    /**
     * Formatter the value to studly case.
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function studlyCase()
    {
        $this->value = Str::studly($this->value);

        return $this;
    }

    /**
     * Formatter the value to its plural form.
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function plural()
    {
        $this->value = Str::plural($this->value);

        return $this;
    }

    /**
     * Formatter the value to a pretty phone format (xxx) xxx-xxxx
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function phone()
    {
        $this->throwExceptionIfNonNumeric('phone');

        if (strlen($this->value) == '11') {
            $this->value = substr($this->value, 0, 1).'('.substr($this->value, 1, 3).')'.substr($this->value, 4, 3).'-'.substr($this->value, 7);
        } elseif (strlen($this->value) == '10') {
            $this->value = '('.substr($this->value, 0, 3).')'.substr($this->value, 3, 3).'-'.substr($this->value, 6);
        }
        return $this;
    }
    /**
     * Encrypt our value
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function encrypt()
    {
        $this->value = encrypt($this->value);

        return $this;
    }

    /**
     * Decrypt our value
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function decrypt()
    {
        $this->value = decrypt($this->value);
        return $this;
    }
    /**
     * Hash our value with bcrypt
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function bcrypt()
    {
        $this->value = bcrypt($this->value);
        return $this;
    }

    /**
     * Limit the string by number of specified characters
     * and append a string
     * @param  String $limit
     * @param  String $append
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function limit($limit = null, $append = '')
    {
        if ($limit === null) {
            $limit = strlen($this->value);
        }
        $this->value = Str::limit($this->value, $limit, $append);
        return $this;
    }
    /**
     * Replace all the given characters with the given character
     * @param  String $search
     * @param  String $replace
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function replace($search, $replace = '')
    {
        $this->value = str_replace($search, $replace, $this->value);

        return $this;
    }
    /**
     * Remove everything but numbers from the value
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function onlyNumbers()
    {
        $this->value = preg_replace('/[^0-9]/', '', $this->value);

        return $this;
    }
    /**
     * Convert the value to a url using laravels url helper
     * @return CollabCorp\Formatter\Formatter
     */
    public function url()
    {
        $this->value = url($this->value);
        return $this;
    }

    /**
     * Remove everything but letters from the value
     * @return CollabCorp\Formatter\Formatter instance
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
     * @return CollabCorp\Formatter\Formatter
     */
    public function onlyAlphaNumeric($allowSpaces = false)
    {
        $regex = $allowSpaces ? "/[^0-9a-zA-Z ]/":"/[^0-9a-zA-Z]/";
        $this->value = preg_replace($regex, "", $this->value);
        return $this;
    }

    /**
     * Remove Leading and ending characters
     * @param  String $trimOff
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function trim($trimOff = ' ')
    {
        $this->value = trim($this->value, $trimOff);

        return $this;
    }
    /**
     * Remove leading characters
     * @param  String $trimOff
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function ltrim($trimOff = ' ')
    {
        $this->value = ltrim($this->value, $trimOff);

        return $this;
    }
    /**
     * Remove ending characters
     * @param  String $trimOff
     * @return CollabCorp\Formatter\Formatter instance
     */
    public function rtrim($trimOff = ' ')
    {
        $this->value = rtrim($this->value, $trimOff);

        return $this;
    }
}
