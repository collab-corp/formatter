# Input Formatter

[![Build Status](https://travis-ci.org/collab-corp/formatter.svg?branch=master)](https://travis-ci.org/collab-corp/formatter)
[![StyleCI](https://styleci.io/repos/119897298/shield?branch=master)](https://styleci.io/repos/119897298)

A package for formatting data/values in PHP.

<p>
  This package eases having to manually format/sanitize alot of data and does so using Laravel's
  <a href="https://laravel.com/docs/5.8/validation#quick-writing-the-validation-logic">
    validation
  </a> syntax, under the hood it utilizes Laravel's support & validation classes. The support for dot notation and wildcard matching and along with some other features makes formatting data a breeze.
</p>


## Installation

`composer require collab-corp/formatter`


## Example:

If you're familiar with Laravel's validation and defining rules, then this will be a breeze for you.

### Define  a whitelist
During its processing, the parsing class will allow any built in php function or autoloaded helper function to
be applied to your data as long as you have <strong>whitelisted</strong> the method, so thats the first step, is defining a whitelist of allowed functions to be called on your data, if using Laravel, consider doing this in a Service Provider:

```php
use CollabCorp\Formatter\FormattedData;


FormattedData::registerCallableWhiteList([
    'trim',
    'rtrim',
    'ucfirst',
    'ucwords',
    'preg_replace',
    'json_encode',
    'to_carbon', // autoloaded helper function that just returns a Carbon instance
]);

```

<p>
  This is simply a security measure, as we dont want to allow all/random functions to be called, especially
  if you build UI related features around this package and use user input to determine your string callables.
</p>

### The data:

From there say you have some ugly data that needs to be formatted:

```php

$data = [
  'first_name'=>'    Jim    ',
  'last_name'=>'   thompson',
  'phone_number'=>'    1234567890SomeLetters    ',
  'date_of_birth'=>'1989-05-20',
  'favorite_numbers'=>[
      "1SomeCharactersForTesting",
      "2SomeCharactersForTesting",
      "3SomeCharactersForTesting"
  ],
  'contact_info'=>[
      'address_one'=>'$$$$$$$$123 some lane st$$$$$$$$$',
      'address_two'=>'   ....321 some other lane st.......',
      'apartment_number'=>'klsadfjaklsd12',
      'email_one'=>'email@example.com',
      'email_two'=>'mail@example.com',
      'po_box'=>'1245'
  ],
  'extra'=> null,
  'more_data'=> '     something',

];

```
### Define the formatting rules:

We can then define some rules/callables to format our data:


```php

$rules = [
    'first_name'=>'trim',
    'last_name'=>'trim|ucfirst',
    'phone_number'=>'trim|preg_replace:/[^0-9]/,,$value', // see extra section to learn what "$value" is
    'date_of_birth'=>'to_carbon|format:m/d/Y', // see extra section on delegating function calls to underlying objects
    'favorite_numbers'=>'preg_replace:/[^0-9]/,,$value',
    'contact_info.address_one'=>'trim:$|ucwords',
    'contact_info.*number'=>'preg_replace:/[^0-9]/,,$value',
    'contact_info.*email*'=>[new FormatValueClass],
    'contact_info.address_two'=>['trim','trim:.',function ($address) {
      return 'Address prefix added via closure to the address: '.$address;
    }],
    'extra'=>'bailIfEmpty|trim|ucfirst', // see extra section on bailing on empty input
    'more_data'=>'bailIfEmpty|trim|ucfirst',
];

```
A couple things to note about defining rules <strong>(also review extra section for more specifics)</strong>:

#### String Rules
<p>
  The most common definition of a rule is a string, it uses the following syntax:
</p>

`"method_name"`

To pass paramaters simply delimit the method name by a `: (colon)`, then provide a comma-delimited list of parameters:


`"method_name:param1,param2"`


 To call multiple functions on an input use a pipe character `|` to delimit the list of functions:


`"method_name:param1|method_two|method_three:param"`

#### Rule Closures

You may use rule closures if you need a little more control over formatting your value:


```php
...,
'inputName'=>[function($value){
  //change value
  return $value;
}],

```


#### Formattable Rule Classes:

You may also use classes to format the value, simply implement the `CollabCorp\Formatter\Contracts\Formattable` contract which has you define `format` function:

```php

<?php

namespace App\Formatters;

use CollabCorp\Formatter\Contracts\Formattable;

class FormatValueClass implements Formattable
{
    /**
     * Format the value.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function format($value)
    {
      //change the value
        return $value;
    }
}

```

### Finally, format my data:
Once you have defined the rules and you want to format your data, simply new up a `FormattedData` and ask for it:

```php
use CollabCorp\Formatter\FormattedData;

$formatter = new FormattedData($data, $rules);

$convertedData = $formatter->get(); //process rules and get the converted data.
```


#### Extra/Specifics:


#### $value keyword:

By default it is assumed that we are passing in a value to a function followed by any parameters you define in your rule,

`eg some_method:param1,param2 -> some_method($value, $param1, $param2);`


Okay cool, but what about if the value needs to be passed in a different order with any other parameters? Not all php functions are the same.
Thats where `$value` comes in. You can specify `$value` when you define parameters:

```php
//rule:
"preg_replace:/[^0-9]/,,$value"

//results in the call being:

preg_replace("/[^0-9]/", "", $value)

//instead of
preg_replace($value, "/[^0-9]/", "")

//had we defined the rule as:
"preg_replace:/[^0-9]/,,"
```

Again, by default value gets passed in first followed by your other defined parameters, use `$value` when this is not the case.


##### Wildcards/Dot Notation:

If you noticed from the above example, we defined a few input keys using dot notation and wildcards, the parsing class supports the ability to specify nested keys using dot notation, that way you can format nested array attributes. You can also conditionally apply rules/functions only matched keys using wildcard asterisk `*`. We assume you know how wildcards work:

`e.g : contact_info.*email* // apply functions to any keys that have the word "email" in it within $data['contact_info']`


##### Delegating functions to object values:
If your value(s) at any point becomes an object, you may specify method's that exist on that object for your value to be processed by. For example, looking at our example above, we defined the following rules to apply to `date_of_birth`:

```php

'date_of_birth'=>'to_carbon|format:m/d/Y',

```

Again, `to_carbon` is just our autoloaded helper function that returns a Carbon instance:

```php
function to_carbon($date)
{
    return \Carbon\Carbon($date);
}

```
When the parsing class calls `to_carbon`, our value is now an instance of `Carbon`, from there we can call any Carbon method we want, in our example we called `format` next. `format` is a method that exists on the Carbon instance.

<strong>
  Note:
</strong>
This makes it convenient to utilize other class methods, however using this method of formatting may make it easier to introduce bugs.


##### Trait:

A trait is available should you prefer a `$this` like syntax:


```php
use CollabCorp\Formatter\FormattedData;

class SomeClass
{
    use FormatsData;

    public function index()
    {
        $formattedData = $this->format($data, $rules);
    }
}

```

##### Bail on empty input:

Sometimes you may want to break out of processing the input if the input is empty. This can be done by defining `bailIfEmpty` in the rule definition for the input in question. Looking at the above example data:

```php
//data:
'extra'=>null
//rule:
'extra'=>'bailIfEmpty|trim|ucfirst',

```

`extra` data will never have `trim` or `ucfirst` applied to it. That's cause `extra` is `null` and when the parsing class gets to the `bailIfEmpty` part, the rule processing is exited.


<strong>Note:</strong> Empty is defined by an empty array, empty string or null value. If you have specific business logic that determines what makes a value "empty", then consider using a closure or formatting class to conditionally determine if your value shoule be formatted.




## Contribute

Contributions are always welcome in the following manner:
- Issue Tracker
- Pull Requests
- Collab Corp Slack(Will send invite)




License
-------

The project is licensed under the MIT license.
