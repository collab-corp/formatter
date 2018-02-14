<?php

use Carbon\Carbon;
use CollabCorp\Formatter\Tests\TestCase;
use CollabCorp\Formatter\Formatter;

class MultipleFormattingTest extends TestCase
{
    /**
     * @test
     */
    public function formatterCanConvertMultipleInputUsingExplicitKeyNames()
    {
        $formatters=[

            'name'=>'titleCase',
            'phone'=>'onlyNumbers|phone',
            'ssn'=>'ssn',
            'slug'=>'slug',
            'price'=>'decimals:2|start:$',
            'percent'=>'percentage:2|finish:%',
            'items'=>'onlyNumbers|add:2|finish:%'

        ];

        $request=[

            'name'=>'peter parker',
            'phone'=>'sdfdfsdf1234567890',
            'ssn'=>'123456789',
            'slug'=>'about us',
            'price'=>'300',
            'percent'=>'30',
            'items'=>[

                'test123',
                'test321',
                'test456',

            ]
        ];

        $request = Formatter::convert($formatters, $request);

        $this->assertEquals('Peter Parker', $request['name']);
        $this->assertEquals('(123)456-7890', $request['phone']);
        $this->assertEquals('123-45-6789', $request['ssn']);
        $this->assertEquals('about-us', $request['slug']);
        $this->assertEquals('$300.00', $request['price']);
        $this->assertEquals('0.30%', $request['percent']);
        $this->assertEquals('125%', $request['items'][0]);
        $this->assertEquals('323%', $request['items'][1]);
        $this->assertEquals('458%', $request['items'][2]);
    }


    /**
     * @test
     */
    public function formatterCanConvertMultipleInputUsingPatternKeyNames()
    {
        $formatters=[

            'name*'=>'titleCase',
            '*phone*'=>'onlyNumbers|phone',
            '*number'=>'add:2|multiply:2',
            '*items*'=>'onlyNumbers|add:2|finish:%',
            'explicit'=>'finish:foo',
            'nest.phone'=>'onlyNumbers',
            'nest.foo'=>'add:3'

        ];

        $request=[

            'name'=>'peter parker',
            'something_name'=>'peter parker',//this should be the same cause were only formatting things that start with *name
            'phone'=>'sdfdfsdf1234567890',
            'cell_phone'=>'sdfdfsdf1234567890',
            'number_something'=>'2', //this should be the same cause were only formatting things that end with *number
            'something_number'=>'2',
            'some_items_foobar'=>[

                'test123',
                'test321',
                'test456',

            ],
            'explicit'=>'',
            'nest'=>[
                'phone'=>'(830)374-5517', //these should be formatted
                'foo'=>'34', //these should be formatted
            ]
        ];


        $request = Formatter::convert($formatters, $request);


        $this->assertEquals('Peter Parker', $request['name']);
        $this->assertEquals('peter parker', $request['something_name']);
        $this->assertEquals('(123)456-7890', $request['phone']);
        $this->assertEquals('(123)456-7890', $request['cell_phone']);
        $this->assertEquals('2', $request['number_something']);
        $this->assertEquals('8', $request['something_number']);
        $this->assertEquals('125%', $request['some_items_foobar'][0]);
        $this->assertEquals('323%', $request['some_items_foobar'][1]);
        $this->assertEquals('458%', $request['some_items_foobar'][2]);
        $this->assertEquals('8303745517', $request['nest']['phone']);
        $this->assertEquals('37', $request['nest']['foo']);
        $this->assertEquals('foo', $request['explicit']);
    }
}
