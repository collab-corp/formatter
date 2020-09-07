<?php

use CollabCorp\Formatter\DataFormatter;
use CollabCorp\Formatter\Support\ValueFormatter;
use CollabCorp\Formatter\Tests\TesterFormatClass;

/*
|--------------------------------------------------------------------------
| A file to play around with the package.
|--------------------------------------------------------------------------
|
*/

require __DIR__.'/vendor/autoload.php';

$data = [
  'first_name'=>'    Jim    ',
  'last_name'=>'   thompson',
  'phone_number'=>'1234567890SomeLetters',
  'date_of_birth'=>"2018-04-01",
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

$rules = [
    'first_name'=>'trim',
    'last_name'=>'trim|ucfirst',
    'phone_number'=>'preg_replace:/[^0-9]/,,:value:', // see extra section to learn what "$value" is
    'date_of_birth'=>'?|to_carbon|.format:m/d/y', // see extra section on delegating function calls to underlying objects
    'favorite_numbers'=>'preg_replace:/[^0-9]/,,value',
    'contact_info.address_one'=>'trim:$|ucwords',
    'contact_info.*number'=>'preg_replace:/[^0-9]/,,:value:',
    'contact_info.*email*'=>[new TesterFormatClass],
    'contact_info.address_two'=>[function ($address) {
      return 'Address Two Is: '.$address;
    }],
];

$formatter = new DataFormatter($data, $rules);

dd($formatter->get());