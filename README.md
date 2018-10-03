# Formatter
[![Build Status](https://travis-ci.org/collab-corp/formatter.svg?branch=master)](https://travis-ci.org/collab-corp/formatter)
[![StyleCI](https://styleci.io/repos/119897298/shield?branch=master)](https://styleci.io/repos/119897298)

A package for formatting values in Laravel.

We often find ourselves converting request input to meet certain formats. We do it before validation, maybe after validation, or we do it in our mutators. This package was designed to ease that process and to keep our controller/model code cleaner. This package mostly uses laravel's helper methods on top of its own custom methods, there is also some formatting using the Carbon date library.

## Installation

`composer require collab-corp/formatter`

## Binding / Package Discovery

By default this package is auto discovered in Laravel 5.5 with its service provider automatically binding a Formatter using the `collab-corp.formatter` key binding. You can resolve a binding as follows:

```php

$formatter = app('collab-corp.formatter'); //returns Formatter instance with a default null value

$formatter = $formatter->setValue('foobar');


```

For lower versions of laravel you will have to manually register `\CollabCorp\Formatter\FormatterServiceProvider::class`
in your `/config/app.php` file.




## Creating Formatters:

There are multiple ways to create a formatter:


```php

use CollabCorp\Formatter\Formatter;

//You can simply new up a formatter
new Formatter('yourValue');

//or you could use static create method
Formatter::create("yourValue");

//or us formatter helper
formatter('yourValue');

```

## Converting Formatter Values

Once you have insantiated your formatter, you can call any conversion/formatter <a href="#methods">methods</a> as detailed below.

Here are a few examples using laravel's mutators :

```php

<?php

use CollabCorp\Formatter\Formatter;
//etc.
class SomeModel extends Model{

    public function getProductPriceAttribute(){
        //format our number to 2 decimal places or however many places you want
        return new Formatter($this->attributes['price'])->decimals(2)->get();

    }

    public function getPhoneNumberFormattedAttribute(){
        //format a 10 or 11 digit number to be in parenthesis US format:  x(xxx)xxx-xxxx
        return new Formatter($this->attributes['phone_number'])->phone()->get();

    }

}

````

## Method Chaining

These above examples are nothing special, you could do the same  with a simple method/function call yourself. The real usefulness of this package is the ability to method chain and process many conversions on a input:

```php
<?php
    //convert our phone number to a parenthesis format but first make sure to strip any non numeric characters off first
    $result = new Formatter($request->phone_number)
                        ->onlyNumbers()
                        ->phone()
                        ->get();

   //another example
   $result = new Formatter(2)
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


## Multiple Values
The formatter class also supports formatting multiple values on a given instance:

```php
new Formatter(['value', 'value2'...]);
```

### Nested Arrays

When processing nested arrays when using multiple values like noted above, this package recursively processes nested arrays as well. Meaning each of value of each nested array withing your array of values will be processed as well:

```php


$values = (new Formatter(['123something', ["foo123","bar456",'baz678']]))->onlyNumbers()->get();

//returns ['123', ['123','456','678']]


```


## Mass Conversions

Another useful and probably the best reason for use of this package is processing multiple conversion/formatter methods on a given array of input such as the request.The workflow for this is very similiar to laravel validation, so it should feel very natural to you. Here's an example of having middleware automatically format/convert input before the request hits the controller:


```php

 <?php

    public function handle($request, Closure $next, $guard = null)
    {

          $formatters=[
              //convert to parenthesis phone format after stripping all non numeric characters
              'phone'=>'onlyNumbers|phone',
              //format to 2 decimal places
              'price'=>'decimals:2',
               // trim off % signs,convert to decimal percent with 2 decimal places.
              'tax'=>'trim:%|percentage:2',
              // make this input slug friendly
              'page'=>'slug'

          ];

          //returns collection of new input converted.
          $convertedInput = Formatter::convert($request->all(),$formatters);

          //replace existing request data with new converted data,
          $request->replace($convertedInput->all());

          return $next($request);
    }


```
Very similiar to laravel validation right?


### Mass Conversion Closure/Objects

For custom conversion of input, you may use closures and class objects that implement `CollabCorp\Formatter\Contracts\Convertible` :





```php
$formatters=[

    'some_input'=>[new SomeConvertingClass, 'slug'],
    'something_else'=>function($value, $data){

       //change value
       return $value;
    },


];


$convertedInput = Formatter::convert($request->all(),$formatters);


```

That class must implement `convert`:


```php

<?php

namespace App\Converters;
use CollabCorp\Formatter\Contracts\Convertible;

class SomeConverttingClass implements Convertible
{

    /**
     * Convert the given value as needed.
     * @param  mixed  $value The value being converted
     * @param  array $data The data being processed
     * @return mixed
     */
    public function convert($value, $data)
    {
        $value = 'change value';

        return $value;
    }
}


```


## ConvertsInput Trait

If you'd like to be able to mass convert input in your controllers, out of the box we provide a trait `ConvertsInput` for convenience and quickly calling the `Formatter::convert()` method as shown above:

```php


<?php
use CollabCorp\Formatter\Concerns\ConvertsInput;
class SomeController{
    use ConvertsInput;


}

```

Then you can simply just call as needed `convert` as needed:

```php
<?php
...


public store(Request $request){

    //array and collections return converted data, $request object will return this
    $newData = $this->convert($request->all(), [

        'phone'=>'onlyNumbers|phone',

        'price'=>'decimals:2',

        'tax'=>'trim:%|percentage:2',

        'page'=>'slug'

    ]);


    //as usual

}


```
<strong>
 Note:
</strong>If you pass in the request object without calling all(), the data will be replaced  for you automatically,however, you can specify not to have the request data replaced by passing in false to the 3rd parameter. Of course this is only if the
 given variable is a `\Illuminate\Http\Request` object:


```php

$this->conver($request, [...], false);
```


You may also reuse this trait outside of controller for your models or collections. Really the trait is usable for array input  in general. Simply pass in the array wanted. If you pass in a collection, the trait will extract the underlying array. If you pass in something that is `Arrayable` we will call `toArray`.







### Ignore empty strings/null or empty arrays

By default, when you  call formatter methods on a value, the class will run regardless of value. In some cases however, you may not want formatter to run conversions on an input that is an empty string, null or an empty array. Consider the following scenario:

You have a form to allow your users to update their email,username and password. The password field may be nullable and you will only update it if its not null or empty. You may have set up a converter to automatically convert the password to a hashed string


```php

//ex scenario: conversions before validation
$this->convert($request, [
    'password'=>"bcrypt",
]);
```

However, this will run `bcrypt` on the value even if its an empty string, resulting in a password getting set to a hashed value of an empty string, which can be a risk to updating a user password accidently. In these cases, you can toggle the formatter to ignore empty strings,null and empty arrays:

```php
Formatter::ignoreIfValueIsEmpty(true)

$this->convert($request, [
    'password'=>"bcrypt",
]);

$pw = $request->password;

//will only be bcrypted if the request value was not an empty string or null value.
dd($pw);

//turn off
Formatter::ignoreIfValueIsEmpty(false);

//other formatter calls whos values can be empty
```



You may also specify for a certain input to be skipped if the value is empty in mass conversions:

```php
$this->convert($request, [
   '*password*'=>"bailIfEmpty|bcrypt",
]);


```
In this case `bailIfEmpty` will be executed and password will not be hashed if it's empty string or null. The conversion will simply move on to the next request input key.

### Custom Empty

Of course with more complex values or maybe specific logic, you may have custom checks for determining what considers
a value "empty". You can you classes to do this:

```php

$this->convert($request, [
   'some_input'=>[new MeetsRequirements, 'bcrypt'],
]);

```

Simply have this class implement `CollabCorp\Formatter\Contracts\CheckForEmpty` interface:


```php
<?php

namespace App\EmptyChecks;

use CollabCorp\Formatter\Contracts\CheckForEmpty;

class MeetsRequirements implements CheckForEmpty
{
    /**
     * Check if the given value is "empty"
     * @param  mixed  $value The value being checked
     * @param  array  $data The array data being processed
     * @return boolean
     */
    public function isEmpty($value, $data):bool
    {
        //logic

        //return true|false
    }
}


```


# Methods are whitelisted

By default all formatter methods are checked against a whitelist at run time. This is just a precautionary measure to avoid allowing client side requests to make calls to formatter class methods. Example:Consider a UI that allows clients to determine formatter methods. The same goes for macro added methods. If the method is not in the whitelist or was not added via the macro trait, then naturally they are considered undefined methods.

## Patterns
The above example is being explicit in its request keys, but you could also specify pattern input keys using asterisk to  match input keys and process them if they match the pattern:

```php
<?php

    $formatters=[
        '*phone'=>'onlyNumbers|phone', //run methods on any input that has a name that ends with phone
        'phone*'=>'onlyNumbers|phone', //run methods on any input that has a name that starts with phone
        '*phone*'=>'onlyNumbers|phone', //run methods on any input that has a name that has the word phone in it.
    ];

    $convertedInput = Formatter::convert($formatters, $request->all());

```

<strong>Note: Pattern matching with nested associated arrays is not supported. You must be explicit with nested associative arrays formatters:</strong>


```php


$formatters=[

   'applicant.*name*'=>'titleCase' // this is not supported
   'applicant.name'=>'titleCase' //this is. Were explicitly telling the formatter to format $request->input('applicant.name');

];

```

## Macroable

The `Formatter` class is macroable which means you can add extra formatting methods on run time. Heres an example:

```php
Formatter::macro('hello', function () {
    return $this->setValue("Hello ". $this->value));
});

$upper= (new Formatter("World"))->hello()->get(); // "Hello World"

```


##  Methods

####  String Formatters:

<ul>
    <li>
        <a href="#ssn">ssn</a>
    </li>
    <li>
        <a href="#phone">phone</a>
    </li>
    <li>
        <a href="#truncate">truncate</a>
    </li>
    <li>
        <a href="#finish">finish</a>
    </li>
    <li>
        <a href="#start">start</a>
    </li>
    <li>
        <a href="#prefix">prefix</a>
    </li>
    <li>
        <a href="#suffix">suffix</a>
    </li>
    <li>
        <a href="#insertevery">insertEvery</a>
    </li>
    <li>
        <a href="#camelcase">camelCase</a>
    </li>
    <li>
        <a href="#snakecase">snakeCase</a>
    </li>
    <li>
        <a href="#titlecase">titleCase</a>
    </li>
    <li>
        <a href="#kebabcase">kebabCase</a>
    </li>
    <li>
        <a href="#studlycase">studlyCase</a>
    </li>
    <li>
        <a href="#singlespacebetweenwords">singleSpaceBetweenWords</a>
    </li>
    <li>
        <a href="#slug">slug</a>
    </li>
    <li>
        <a href="#plural">plural</a>
    </li>
    <li>
        <a href="#limit">limit</a>
    </li>
    <li>
        <a href="#explode">explode</a>
    </li>
    <li>
        <a href="#encrypt">encrypt</a>
    </li>
    <li>
        <a href="#decrypt">decrypt</a>
    </li>
    <li>
        <a href="#bcrypt">bcrypt</a>
    </li>
    <li>
        <a href="#replace">replace</a>
    </li>
    <li>
        <a href="#onlyalphanumeric">onlyAlphaNumeric</a>
    </li>
    <li>
        <a href="#onlynumbers">onlyNumbers</a>
    </li>
    <li>
        <a href="#onlyletters">onlyLetters</a>
    </li>
    <li>
        <a href="#trim">trim</a>
    </li>
    <li>
        <a href="#ltrim">ltrim</a>
    </li>
    <li>
        <a href="#label">label</a>
    </li>
    <li>
        <a href="#rtrim">rtrim</a>
    </li>
     <li>
        <a href="#tobool">toBool</a>
    </li>
    <li>
        <a href="#toupper">toUpper</a>
    </li>
    <li>
        <a href="#tolower">toLower</a>
    </li>
    <li>
        <a href="#url">url</a>
    </li>
</ul>

### Math Formatters

<ul>
    <li>
        <a href="#decimals">decimals</a>
    </li>
    <li>
        <a href="#add">add</a>
    </li>
    <li>
        <a href="#subtract">subtract</a>
    </li>
    <li>
        <a href="#multiply">multiply</a>
    </li>
    <li>
        <a href="#divide">divide</a>
    </li>
    <li>
        <a href="#power">power</a>
    </li>
    <li>
        <a href="#percentage">percentage</a>
    </li>
</ul>

### Date Formatters
Note: These simply are methods called using the Carbon Library. These are the only available methods on the Formatter:
<ul>
    <li>
        <a href="#tocarbon">toCarbon</a>
    </li>
    <li>
        <a href="#format">format</a>
    </li>
    <li>
        <a href="#settimezone">setTimezone</a>
    </li>
    <li>
        <a href="#addyears">addYears</a>
    </li>
    <li>
        <a href="#addmonths">addMonths</a>
    </li>
    <li>
        <a href="#addweeks">addWeeks</a>
    </li>
    <li>
        <a href="#adddays">addDays</a>
    </li>
    <li>
        <a href="#addhours">addHours</a>
    </li>
    <li>
        <a href="#addminutes">addMinutes</a>
    </li>
    <li>
        <a href="#addseconds">addSeconds</a>
    </li>
    <li>
        <a href="#subyears">subYears</a>
    </li>
    <li>
        <a href="#submonths">subMonths</a>
    </li>
    <li>
        <a href="#subweeks">subWeeks</a>
    </li>
    <li>
        <a href="#subdays">subDays</a>
    </li>
    <li>
        <a href="#subhours">subHours</a>
    </li>
    <li>
        <a href="#subminutes">subMinutes</a>
    </li>
    <li>
        <a href="#subseconds">subSeconds</a>
    </li>
</ul>

## String Methods

* ### ssn
    convert a 9 numeric value to a social security format:
    ```php
    //'123-45-6789'
    Formatter::create('123456789')->ssn()->get();
    ```

 * ### phone
    convert a 10 or 11 numeric value to a parenthesis format:
    ```php
    //'(123)456-7890'
    Formatter::create('1234567890')->phone()->get();
    //'1(123)456-7890'
    Formatter::create('11234567890')->phone()->get();
* ### truncate
    truncate off the specified number of characters of the value:
    ```php
    //'foo'
    Formatter::create('foobar')->truncate(3)->get();
    ```
* ### finish
    add the specified character to the end of the string if it doesnt already contain it:
    ```php
    //'foobar'
    Formatter::create('foo')->finish('bar')->get();
    //'foobar'
    Formatter::create('foobar')->finish('bar')->get();
    ```

* ### start
    add the specified character to the start of the string if it doesnt already contain it:
    ```php
    //'foobar'
    Formatter::create('foobar')->start('foo')->get();
    //'foobar'
    Formatter::create('bar')->start('foo')->get();
    ```

 * ### prefix
    add the specified character to the start of the string:
    ```php
    //'foofoobar'
    Formatter::create('foobar')->prefix('foo')->get();
    ```
  * ### suffix
    add the specified character to the end of the string:
    ```php
    // 'foobarfoo'
    Formatter::create('foobar')->suffix('foo')->get();
    ```
  * ### insertEvery
    add the specified character every nth characters:
    ```php
    // '1234 5678 9012  3456'
    Formatter::create('1234567890123456')->insertEvery(4, " ")->get();
    ```
  * ### camelCase
    convert value to camel case:
    ```php
    //'fooBar'
    Formatter::create('foo bar')->camelCase()->get();
    ```
  * ### snakeCase
    convert value to snake case:
    ```php
    //'foo_bar'
    Formatter::create('foo bar')->snakeCase()->get();
    ```
  * ### titleCase
    convert value to snake case:
    ```php
    //'Foo Bar'
    Formatter::create('foo bar')->titleCase()->get();
    ```
  * ### kebabCase
    convert value to kebab case:
    ```php
    // 'foo-bar'
    Formatter::create('foo bar')->kebabCase()->get();
    ```
  * ### studlyCase
    convert value to kebab case:
    ```php
    //'FooBar'
    Formatter::create('foo bar')->studlyCase()->get();
    ```
  * ### singleSpaceBetweenWords
    Trim all white space between words to a single space:
    ```php
    //'A sentence with lots of space between words'
    Formatter::create('A sentence     with   lots of   space   between words')->singleSpaceBetweenWords()->get();
    ```
  * ### slug
    convert value to a slug friendly string:
    ```php
    //'foo-bar'
    Formatter::create('foo bar')->slug()->get();
    ```
  * ### plural
    convert value to its plural form:
    ```php
    //'children'
    Formatter::create('child')->plural()->get();
    ```
  * ### limit
    limit the string the first n of characters:
    ```php
    //'child'
    Formatter::create('children')->limit(5)->get();
    ```
  * ### encrypt
    encrypt the value:
    ```php
    //'{some encrypted string}'
    Formatter::create('someString')->encrypt()->get();
    ```
  * ### explode
    Explode the string into an array with the given delimiter(default is comma):
    ```php
    //['foo', 'bar', 'baz']
    Formatter::create('foo|bar|baz')->explode('|')->get();
    ```
  * ### decrypt
    decrypt the value:
    ```php
    //the original string
    Formatter::create('{some encrypted string}')->decrypt()->get();
    ```
  * ### bcrypt
    hash the value with bcrypt:
    ```php
    //'{some hashed string result}'
    Formatter::create('secret1')->bcrypt()->get();
    ```
  * ### replace
    replace the given character with the given replacement character, defaults to empty string for replacement:
    ```php
    //'bar'
    Formatter::create('foobar')->replace('foo')->get();
    //'poobar'
    Formatter::create('foobar')->replace('foo', 'poo')->get();
    ```
  * ### onlyAlphaNumeric
    replace non alphanumeric characters, including spaces, unless specified by 2nd parameter:
    ```php
    //'foobar123test'
    Formatter::create('foobar123 &$*&$(#(*test')->onlyAlphaNumeric()->get();
    //'foobar123 test 123'
    Formatter::create('foobar123 &$*&$(#(*test 123',true)->onlyAlphaNumeric()->get();
    ```
  * ### onlyNumbers
    removes all characters that are not numbers from the value:
    ```php
    //'123'
    Formatter::create('sfsdfs123')->onlyNumbers()->get();
    ```
  * ### onlyLetters
    removes all characters that are not letters from the value:
    ```php
    //'test'
    Formatter::create('#(@)!@test123')->onlyLetters()->get();
    ```
  * ### trim
    removes all leading and ending spaces/characters from the value:
    ```php
    //'something'
    Formatter::create('   something     ')->trim()->get();
    //something
    Formatter::create('####something####')->trim("#")->get();
    ```
  * ### ltrim
    removes all leading spaces/characters from the value:
    ```php
    //'something'
    Formatter::create('   something')->trim()->get();
    //something####
    Formatter::create('####something####')->trim("#")->get();
    ```
  * ### label
    Convert the string to a "pretty label":
    ```php
    //'Some Column Name'
    Formatter::create('some_column_name')->label()->get();
    //something####
    Formatter::create('####something####')->trim("#")->get();
    ```
  * ### rtrim
    removes all ending spaces/characters from the value:
    ```php
    //'    something'
    Formatter::create('    something    ')->rtrim()->get();
    //####something
    Formatter::create('####something####')->rtrim("#")->get();
    ```
  * ### toBool
     Convert a string value to its boolean representation.
    Note: `'true|1'` and `'false|1'` will be converted  to their boolean values, other
    strings will be processed using filter_var()
    ```php
    //true
    Formatter::create('true')->toBool()->get();
    ```

  * ### toUpper

    Convert the string to uppercase:

    ```php
    //'SOMETHING'
    Formatter::create('something')->toUpper()->get();

 * ### toLower

    Convert the string to lowercase:

    ```php
    //'something'
    Formatter::create('SOMETHING')->toLower()->get();
    ```

  * ### url
    Creates a url string of your value using laravel's url() helper:
    ```php
    //'http://{APP_URL}/something'
    Formatter::create('something')->url()->get();
    ```

    ##

    ##  Math Methods
    <strong>Note:</strong>
    The math formatter/conversion methods  in this package makes use of the `bcmath` extension for precision math. In the event that the    `bcmath` functions are unavailable, then it will fall back to native math operations requiring php 5.6+. In addition,when using `bcmath`, all values are scaled to be 64 decimal places for precision math. If you want to format how many decimal places, consider chaining `decimals` method.

  * ### decimals
    format a number to have the speficied number of decimal places:
    ```php
    //22.00
    Formatter::create(20)->add(2)->decimals(2)->get();
    ```
  * ### add
    add a given number to the current numeric value.Automatically scales 0 decimal places unless specified as 2nd param:
    ```php
    //22
    Formatter::create(20)->add(2)->get();
    //22.00
    Formatter::create(20)->add(2,2)->get();
    ```
  * ### subtract
    subtract a given number from the current numeric value. Automatically scales 0 decimal places unless specified as 2nd param:
    ```php
    //18
    Formatter::create(20)->subtract(2)->get();
    //18.00
    Formatter::create(20)->subtract(2,2)->get();
    ```
  * ### divide
    divide a the current numerica value by a given number. Automatically scales 0 decimal places unless specified as 2nd param:
    ```php
    //18
    Formatter::create(20)->divide(2)->get();
    //18.00
    Formatter::create(20)->divide(2,2)->get();
    ```
 * ### power
    raise the current value by a given power. Automatically scales 0 decimal places unless specified as 2nd param:
    ```php
    //400
    Formatter::create(20)->power(2)->get();
    //400.00
    Formatter::create(20)->power(2,2)->get();
    ```
 * ### percentage
    convert the value to a decimal percentage. Automatically scales 2 decimal places unless specified as 2nd param:
    ```php
    //0.20
    Formatter::create(20)->percentage()->get();
    //0.200
    Formatter::create(20)->percentage(2,2)->get();
    ```

  ##

  ## Date Methods

* ### toCarbon
    convert the value to a Carbon\Carbon instance:
    ```php
    //Carbon instance "2030-12-22 00:00:00"
    Formatter::create("12/22/2030")->toCarbon()->get();
    ```
* ### format
    convert the Carbon\Carbon instance to a specified date/time string:
    ```php
    $formatter = Formatter::create("12/22/2030")->toCarbon();
    //"December 22, 2030 00:00:00"
    $formatter->format('F d, Y')->get();
    ```

* ### setTimezone
    set the specified timezone on the Carbon instance:
    ```php
    $formatter = Formatter::create("12/22/2030")->toCarbon();
    //Carbon instance with given timezone set "2030-12-22 00:00:00".
    $formatter->setTimezone('America/Toronto')->get();
    ```
* ### addYears
    add the given number of years to  the Carbon instance:
    ```php
    $formatter = Formatter::create("12/22/2030")->toCarbon();
    //Carbon instance "2032-12-22 00:00:00".
    $formatter->addYears(2)->get();
    ```
 * ### addMonths
    add the given number of months to  the Carbon instance:
    ```php
    $formatter = Formatter::create("12/22/2030")->toCarbon();
    //Carbon instance "2031-02-22 00:00:00".
    $formatter->addMonths(2)->get();
    ```
  * ### addWeeks
    add the given number of weeks to  the Carbon instance:
    ```php
    $formatter = Formatter::create("12/22/2030")->toCarbon();
    //Carbon instance "2031-01-04 00:00:00".
    $formatter->addWeeks(2)->get();
    ```
  * ### addDays
    add the given number of days to  the Carbon instance:
    ```php
    $formatter = Formatter::create("12/22/2030")->toCarbon();
    //Carbon instance "2030-12-24 00:00:00".
    $formatter->addDays(2)->get();
    ```
  * ### addHours
    add the given number of hours to  the Carbon instance:
    ```php
    $formatter = Formatter::create("2030-12-22 02:02:02")->toCarbon();
    //Carbon instance "2030-12-22 04:02:02".
    $formatter->addHours(2)->get();
    ```
  * ### addMinutes
    add the given number of minutes to  the Carbon instance:
    ```php
    $formatter = Formatter::create("2030-12-22 02:02:02")->toCarbon();
    //Carbon instance "2030-12-22 02:04:02".
    $formatter->addMinutes(2)->get();
    ```
  * ### addSeconds
    add the given number of seconds to  the Carbon instance:
    ```php
    $formatter = Formatter::create("2030-12-22 02:02:02")->toCarbon();
    //Carbon instance "2030-12-22 02:02:04".
    $formatter->addSeconds(2)->get();
    ```
  * ### subYears
    sub the given number of years to  the Carbon instance:
    ```php
    $formatter = Formatter::create("12/22/2030")->toCarbon();
    //Carbon instance "2028-12-22 00:00:00".
    $formatter->subYears(2)->get();
    ```
 * ### subMonths
    subtract the given number of months to  the Carbon instance:
    ```php
    $formatter = Formatter::create("12/22/2030")->toCarbon();
    //Carbon instance "2030-10-22 00:00:00".
    $formatter->subMonths(2)->get();
    ```
  * ### subWeeks
    subtract the given number of weeks to  the Carbon instance:
    ```php
    $formatter = Formatter::create("12/22/2030")->toCarbon();
    //Carbon instance "2030-12-08 00:00:00".
    $formatter->subWeeks(2)->get();
    ```
  * ### subDays
    subtract the given number of days to  the Carbon instance:
    ```php
    $formatter = Formatter::create("12/22/2030")->toCarbon();
    //Carbon instance "2030-12-20 00:00:00".
    $formatter->subDays(2)->get();
    ```
  * ### subHours
    subtract the given number of hours to  the Carbon instance:
    ```php
    $formatter = Formatter::create("2030-12-22 02:02:02")->toCarbon();
    //Carbon instance "2030-12-22 00:02:02".
    $formatter->subHours(2)->get();
    ```
  * ### subMinutes
    subtract the given number of minutes to  the Carbon instance:
    ```php
    $formatter = Formatter::create("2030-12-22 02:02:02")->toCarbon();
    //Carbon instance "2030-12-22 02:00:02".
    $formatter->subMinutes(2)->get();
    ```
  * ### subSeconds
    subtract the given number of seconds to  the Carbon instance:
    ```php
    $formatter = Formatter::create("2030-12-22 02:02:02")->toCarbon();
    //Carbon instance "2030-12-22 02:02:00".
    $formatter->subSeconds(2)->get();
    ```


## Contribute

Contributions are always welcome in the following manner:
- Issue Tracker
- Pull Requests
- Collab Corp Slack(Will send invite)




License
-------

The project is licensed under the MIT license.
