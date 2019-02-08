# Laravel Input Formatter
[![Build Status](https://travis-ci.org/collab-corp/laravel-input-formatter.svg?branch=master)](https://travis-ci.org/collab-corp/laravel-input-formatter)
[![StyleCI](https://styleci.io/repos/119897298/shield?branch=master)](https://styleci.io/repos/119897298)

A package for formatting data/values in Laravel.

We often find ourselves converting request input/data to meet certain formats. We do it before validation, maybe after validation, or we do it in our mutators. This package was designed to ease that process and to keep our controller/model code cleaner. This package uses our `input-formatter` package to do the formatting of data, this package simply extends
the formatter class from that package and adds Laravel specific helpers/methods. See that base package here:

<a target="_blank" href="https://github.com/collab-corp/input-formatter">collab-corp/input-formatter</a>

## Installation

`composer require collab-corp/formatter`


## Creating Data Formatters:

There are multiple ways to create a formatter:


```php

use CollabCorp\LaravelInputFormatter\DataFormatter;

//You can simply new up a formatter
new DataFormatter('yourValue');

//or you could use static create method
DataFormatter::create("yourValue");

//or use formatter helper
formatter('yourValue');

```

## Converting Formatter Values

Once you have insantiated your formatter, you can call any conversion/formatter method available
from the base Formatter class in our `input-formatter` package:

<a target="_blank" href="https://github.com/collab-corp/input-formatter/blob/master/src/Formatter/Formatter.php">
    collab-corp/input-formatter: Formatter class.
</a>

and those available in the `\CollabCorp\LaravelInputFormatter\DataFormatter` class of this package.
Really the only difference between the two is that this package contains laravel specific helper/methods
and a macroable trait.


Here are some simple examples of using formatters in model mutators.

```php

<?php

use \CollabCorp\LaravelInputFormatter\DataFormatter;
//etc.
class SomeModel extends Model{

    public function getProductPriceAttribute(){
        //format our number to 2 decimal places or however many places you want
        return new DataFormatter($this->attributes['price'])->decimals(2)->get();

    }

    public function getPhoneNumberFormattedAttribute(){
        //format a 10 or 11 digit number to be in parenthesis US format:  x(xxx)xxx-xxxx
        return new DataFormatter($this->attributes['phone_number'])->phone()->get();

    }

}

````

# Method Chaining

These above examples are nothing special, you could do the same  with a simple method/function call yourself. The real usefulness of this package is the ability to method chain and process many conversions on a input:

```php
<?php
    //convert our phone number to a parenthesis format but first make sure to strip any non numeric characters off first
    $result = new DataFormatter($request->phone_number)
                        ->onlyNumbers()
                        ->phone()
                        ->get();

   //another example
   $result = new DataFormatter(2)
                       ->add(2)
                       ->multiply(3)
                       ->finish("%")
                       ->get(); // 12%


```

This makes it convienient to run multiple conversions/formatting for our value. It also makes it easier to keep code clean and having to combine multiple methods yourself. Take the above second example. Doing the method calls manually would look something like this:

```php

    Str::finish(bcmul(bcadd(2, 2), 3), "%");

```

Yuck! right? Although this was a rather small example and not bad, imagine if you had to do 3 other conversions? Keep your code cleaner and more readable with method chaining :D.


# Multiple Values
The base formatter class this package extends also supports formatting multiple values on a given instance:

```php
new DataFormatter(['value', 'value2'...]);
```

## Nested Arrays

When processing nested arrays when using multiple values like noted above, this package recursively processes nested arrays as well. Meaning each of value of each nested array withing your array of values will be processed as well:

```php


$values = (new DataFormatter(['123something', ["foo123","bar456",'baz678']]))->onlyNumbers()->get();

//returns ['123', ['123','456','678']]


```


# Mass Conversions

Another useful and probably the best reason for use of this package is processing multiple conversion/formatter methods on a given array of input such as the request using the validation like syntax you may used to from Laravel's validation features.The workflow for this is very similiar to laravel validation, so it should feel very natural to you. Here's an example of having middleware automatically format/convert input before the request hits the controller:


```php

 <?php

    public function handle($request, Closure $next, $guard = null)
    {

          $formatters=[
              //convert to parenthesis phone format after stripping all non numeric characters
              'phone'=>'onlyNumbers|phone',
              //convert to carbon instance
              'date_of_birth'=>'toCarbon',
              //format to 2 decimal places
              'price'=>'decimals:2',
               // trim off % signs,convert to decimal percent with 2 decimal places.
              'tax'=>'trim:%|decimalPercent:2',
              // make this input slug friendly
              'page'=>'slug'

          ];

          //returns collection of new input converted.
          $convertedInput = DataFormatter::convert($request->all(),$formatters);

          //replace existing request data with new converted data,
          $request->replace($convertedInput->all());

          return $next($request);
    }


```
Very similiar to laravel validation right?


## Mass Conversion Closure/Objects
The base formatter class that this package extends allows you you use closures
and classes that implement the  `CollabCorp\Formatter\Contracts\Formattable` contract in
mass conversions:


```php
$formatters=[

    //using Formattable class:
    'some_input'=>[new SomeConvertingClass, 'slug'],
    //using closure:
    'something_else'=>function($value){

       //change value

       //then return
       return $value;
    },


];


$convertedInput = Formatter::convert($request->all(),$formatters);


```

When implementing the contract, simply specify a `format` method:

```php

<?php

namespace App\Converters;
use CollabCorp\Formatter\Contracts\Formattable;

class SomeConverttingClass implements Formattable
{

    /**
     * Format the given value as needed.
     * @param  mixed  $value
     * @return mixed
     */
    public function format($value)
    {
        $value = 'change value';

        return $value;
    }
}


```


### ConvertsData Trait

If you'd like to be able to mass convert input in your controllers, out of the box we provide a trait `ConvertsData` for convenience and quickly calling the `DataFormatter::convert()` method as shown above:

```php


<?php
use CollabCorp\LaravelInputFormatter\Concerns\ConvertsData;
class SomeController{
    use ConvertsData;


}

```

Then you can simply just call as needed `convert` as needed:

```php
<?php
...


public function store(Request $request)
{

    //new converted data will be returned
    $newData = $this->convert($request->all(), [

        'phone'=>'onlyNumbers|phone',

        'price'=>'decimals:2',

        'tax'=>'trim:%|decimalPercent:2',

        'page'=>'slug'

    ]);


    //as usual

}


```
<strong>
 Note:
</strong>If you pass in the request object without calling `all()`, the data will be replaced for you automatically,however, you can specify not to have the request data replaced by passing in false to the 3rd parameter. Of course this is only if the given variable is a `\Illuminate\Http\Request` object:


```php

$this->convert($request, [...], false);
```


You may also reuse this trait outside of controller for your models or collections. Really the trait is usable for array input  in general. Simply pass in the array wanted. If you pass in a collection, the trait will extract the underlying array. If you pass in something that is `Arrayable` we will call `toArray`.


### Bail if empty value

You may also specify for a certain input to be skipped if the value is empty in mass conversions:

```php
$this->convert($request, [
   'password'=>"bailIfEmpty|bcrypt", //only hash the password if not empty
   'date'=>"bailIfEmpty|toCarbon",//only convert to carbon instance if not empty.
]);


```
In this case when mass conversions hit the `bailIfEmpty` method, it will check if the value being
processed is not an empty array,string or null value. Should you need to have more control over checking
if a value is "empty" or you have custom business logic that determines what makes a value empty, consider
using closures/Formattable classes.

### Patterns
The examples so far have been explicit in its request/input keys, but you could also specify pattern input keys using asterisks to match input keys and process them if they match the pattern:

```php
<?php

    $formatters=[
        '*phone'=>'onlyNumbers|phone', //run methods on any input that has a name that ends with phone
        'phone*'=>'onlyNumbers|phone', //run methods on any input that has a name that starts with phone
        '*phone*'=>'onlyNumbers|phone', //run methods on any input that has a name that has the word phone in it.
    ];

    $convertedInput = Formatter::convert($formatters, $request->all());

```

<strong>Note: Pattern matching with nested associated arrays is not supported.
You must be explicit with nested associative arrays formatters:</strong>


```php


$formatters=[
    // this is not supported
   'applicant.*name*'=>'titleCase'

    //this is. Were explicitly telling the formatter to format $request->input('applicant.name');
   'applicant.name'=>'titleCase'

];

```

## Macroable

The `Formatter` class is macroable which means you can add extra formatting methods at run time. Heres an example:

```php
DataFormatter::macro('hello', function () {
    return $this->setValue("Hello ". $this->value));
});

$hello= (new DataFormatter("World"))->hello()->get(); // "Hello World"

```


## Contribute

Contributions are always welcome in the following manner:
- Issue Tracker
- Pull Requests
- Collab Corp Slack(Will send invite)




License
-------

The project is licensed under the MIT license.
