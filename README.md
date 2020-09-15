
# Input Formatter

[![Build Status](https://travis-ci.org/collab-corp/formatter.svg?branch=master)](https://travis-ci.org/collab-corp/formatter)
[![StyleCI](https://styleci.io/repos/119897298/shield?branch=master)](https://styleci.io/repos/119897298)

A php package for formatting data/values.

<p>
  It is pretty common to have to format/sanitize data to get it in the format we need it to be in. This package
  aims to make it easy to do so without having to dirty up your code with several function calls that leave your code poorly
  nested or hard to read. If you are familiar with
  the Laravel framework, then the syntax should feel familiar. In fact, this package utilizes several support classes used in
  the Laravel framework.
</p>


## Installation

`composer require collab-corp/formatter`


## Use

The most basic use is simple, just pass your value and array of callables that your value should be called against:


```php

use CollabCorp\Formatter\Support\ValueFormatter;

$formatter = new ValueFormatter("   uncle bob   ", ['trim', 'ucwords']);

$formatter->apply()->get(); // returns "Uncle Bob"

```

### Passing Arguments To Callables

You can specify arguments using a `:` followed by a comma delimited list `e.g callable:arg1,arg2`:

```php

function suffix_string($value, $suffix)
{
    return $value.$suffix;
}

$formatter = new ValueFormatter("  Foo  ", ['trim', 'suffix_string:Bar']);

$formatter->apply()->get(); // returns "FooBar"
```

**Note:** Notice that we only specified the `suffix` parameter. The `ValueFormatter` class
automatically passes your value as the first parameter to every function with the exception of
delegation to objects/instances (See Using Instances section below).

<h4>What if value isnt the first parameter to my function?</h4>

No problem, you can specify what order you want your value passed by using the `:value:` placeholder:

For example, `preg_replace` accepts a value to format as the third parameter:

```php

// trim & replace all non numerics with "#"
$formatter = new ValueFormatter("   ABC123   ", [
    'trim',
    'preg_replace:/[^0-9]/,#,:value:'
]);

$formatter->apply()->get(); // returns "###123"

```

### Whitelisting Allowed Callables
By default, all callables are allowed to be called, but if you are dynamically calling callables or
want to add a protection layer, it may be worth specifying what callable functions should be allowed:

```php
$formatter = new ValueFormatter("   uncle bob   ", ['trim', 'ucwords']);

$formatter->allowedCallables(["trim"]); //only trim is allowed

// throws  InvalidArgumentException:
// "Encountered non whitelisted function or non callable [ucwords]"
$formatter->apply()->get();
```

### Using Instances/Object Values & Method Chaining
It is possible to pass an object/instance to the formatter and utilize any methods
on that instance. Using a `.<methodName>` convention you can specify method chaining on that
instance. For example take a [Carbon](https://carbon.nesbot.com/docs/) instance:


```php
$formatter = new ValueFormatter(new Carbon\Carbon('2020-05-24'), [
    '.addDays:1',
    '.format:m/d/Y'
]);

$formatter->apply()->get() // returns "05/25/2020"
```
### Closures/Formattable Classes
You can use closures for formatting your value as well:

```php


$formatter = new ValueFormatter("the value", [
    function($value){
      //do something to the value
      return $value;
    },
    ...
]);

```
Or you can also implement our `CollabCorp\Formatter\Support\Contracts\Formattable` contract and use instances:

```php


use CollabCorp\Formatter\Support\Contracts\Formattable;
use Closure;

class FormatValue implements Formattable
{
    /**
     * Format the value.
     *
     * @param  mixed $value
     * @param  Closure $exit
     * @return mixed
     */
    public function format($value, Closure $exit)
    {
        // do stuff with $value
        // see Optional Formatting section for more on "$exit"
        return $value;
    }
}


$formatter = new ValueFormatter("The value", [
    "trim"
    new FormatValue
    // or using class constant:
     FormatValue::class // formatter will new up the class.
]);

```


### Optional Formatting/Blank Input
Sometimes you may only want to format a value if the value isnt `null` or "blank":
You can specify `?` anywhere in the chain of callables to specify if the formatter
should break out of processing callables, often this should be defined in front of all
your callables:

```php
$formatter = new ValueFormatter(null, [
    "?", //tells the class not to process callables if value is blank
    'to_carbon',
    '.addDays:1',
    '.format:m/d/Y'
]);

$formatter->apply()->get(); // returns original null value

```

**Note:** This packages uses Laravel's [blank](https://laravel.com/docs/8.x/helpers#method-blank) helper to determine blank/empty values. If you have more complicated logic to break out of rules, use a closure or
a `Formattable` class and call the 2nd argument `exit` callback:

```php

$formatter = new ValueFormatter(null, [
    function($value, $exit){
        if($quitProcessing){
            $exit(); // tells formatter to quit processing.
        }
    },
    'to_carbon',
    '.addDays:1',
    '.format:m/d/Y'
]);

```

### Formatting multiple values

So far all the examples have been using a single value, but often times we are working
with much more data from client requests. This is where the `DataFormatter` class is preferred:

```php
use CollabCorp\Formatter\DataFormatter;

$data = [
  'first_name'=>'    jim    ',
  'last_name'=>'   thompson',
  'phone_number'=>'123-456-7890',
  'date_of_birth'=>"1991-05-01",
  ...
];

$callables = [
    'first_name'=>'trim|ucfirst',
    'last_name'=>'trim|ucfirst',
    'phone_number'=>'preg_replace:/[^0-9]/,,:value:',
    'date_of_birth'=>'?|to_carbon|.format:m/d/y',
    ...
];
$formatter = new DataFormatter($data, $callables);

$formatter->apply()->get(); //returns formatted data
```

### Array Input

The `DataFormatter` class makes it easy to work with array input using dot notation:

```php

$data = [
  'contact_info'=>[
    'first_name'=>'    jim    ',
    'last_name'=>'   thompson',
    'phone_number'=>'123-456-7890',
    'address'=>'123 some lane.'
  ]
];

$callables = [
    'contact_info.first_name'=>'trim|ucwords',
    'contact_info.last_name'=>'trim|ucwords',
    'contact_info.phone_number'=>'preg_replace:/[^0-9]/,,:value:',
    'contact_info.address'=>[function ($address) {
      return 'Address Is: '.$address;
    }],
];
$formatter = new DataFormatter($data, $callables);

$formatter->apply()->get();
```

### Wildcard Formatting

Callables can contain wildcards as well:

```php
$data = [
    'first_name'=>'    jim    ',
    'last_name'=>'   thompson',
    ...
];
$callables = [
    //apply to all keys that contain "name"
    '*name*'=>'trim|ucwords',

    //*name - ends with
    //name* - starts with
];

$formatter = new DataFormatter($data, $callables);

$formatter->apply()->get();

```

This includes array input:


```php

$callables = [
    'contact_info.*name*'=>'trim|ucwords',
    // 'contact_info.*name'=>'trim|ucwords',
    // 'contact_info.name*'=>'trim|ucwords',
];

$formatter = new DataFormatter($data, $callables);

$formatter->apply()->get();
```

### Concerns

If you prefer using a concern to easily create formatter instances, we provide the `CollabCorp\Formatter\Support\Concerns\FormatsData` trait for easy access:


```php
<?php

use CollabCorp\Formatter\Support\Concerns\FormatsData;

class SomeClass
{

    use FormatsData;

    public function someFunction()
    {
        //returns ValueFormatter
        $this->formatValue("...", [...])->apply()->get();
        //returns DataFormatter
        $this->formatData([...], [...])->apply()->get();
    }
}

```


## Contribute

Contributions are always welcome in the following manner:
- Issue Tracker
- Pull Requests
- Collab Corp Slack(Will send invite as requested)




License
-------

The project is licensed under the MIT license.
