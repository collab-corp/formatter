<?php

namespace CollabCorp\Formatter\Tests;

use Carbon\Carbon;
use CollabCorp\Formatter\FormattedData;
use CollabCorp\Formatter\Tests\TestCase;
use CollabCorp\Formatter\Tests\TesterFormatClass;

function carbon($date)
{
    return new Carbon($date);
}

class FormatterTest extends TestCase
{
    public function setUp()
    {
        FormattedData::registerCallableWhiteList([
            'trim',
            'rtrim',
            'ucfirst',
            'ucwords',
            'preg_replace',
            'carbon'
        ]);


        $this->data = [
            'first_name'=>'    Jim    ',
            'last_name'=>'   thompson',
            'phone_number'=>'    1234567890SomeLetters    ',
            'date_of_birth'=>'1989-05-20',
            'contact_info'=>[
                'address_one'=>'$$$$$$$$123 some lane st$$$$$$$$$',
                'address_two'=>'   ....321 some other lane st.......',
                'apartment_number'=>'klsadfjaklsd12',
                'email_one'=>'email@example.com',
                'email_two'=>'mail@example.com'
            ]

        ];

        $this->rules = [
            'first_name'=>'trim',
            'last_name'=>'trim|ucfirst',
            'phone_number'=>'trim|preg_replace:/[^0-9]/,,$value',
            'date_of_birth'=>'carbon|isoFormat:m/d/Y',
            'contact_info.address_one'=>'trim:$|ucwords',
            'contact_info.*number'=>'preg_replace:/[^0-9]/,,$value',
            'contact_info.address_two'=>['trim','trim:.',function ($address) {
                return 'Address prefix added via closure to the address: '.$address;
            }],
            'contact_info.*email*'=>[new TesterFormatClass]
        ];

        $this->formatter = new FormattedData($this->data, $this->rules);
    }

    /** @test */
    public function throws_exception_if_non_whitelisted_method_is_called()
    {
        // clear the whitelist
        FormattedData::registerCallableWhiteList([]);

        $this->expectException(\InvalidArgumentException::class);

        $formattedData = $this->formatter->get();
    }

    /** @test */
    public function it_applies_formatting_rules_on_explicit_keys()
    {
        $formattedData = $this->formatter->get();

        $this->assertEquals('Jim', $formattedData['first_name']);
        $this->assertEquals('Thompson', $formattedData['last_name']);
        $this->assertEquals('1234567890', $formattedData['phone_number']);
    }


    /** @test */
    public function it_applies_formatting_rules_on_nested_attributes()
    {
        $formattedData = $this->formatter->get();

        $this->assertEquals('123 Some Lane St', $formattedData['contact_info']['address_one']);

        $this->assertEquals(
            'Address prefix added via closure to the address: 321 some other lane st',
            $formattedData['contact_info']['address_two']
        );
    }

    /** @test */
    public function it_applies_formatting_rules_on_attributes_using_wildcards()
    {
        $formattedData = $this->formatter->get();

        $this->assertEquals('123 Some Lane St', $formattedData['contact_info']['address_one']);

        $this->assertEquals(
            '12',
            $formattedData['contact_info']['apartment_number']
        );

        $this->assertEquals(
            'email@example.com@@@@@hhhhh',
            $formattedData['contact_info']['email_one']
        );

        $this->assertEquals(
            'mail@example.com@@@@@hhhhh',
            $formattedData['contact_info']['email_two']
        );
    }
    /** @test */
    public function it_applies_formatting_rules_on_underlying_objects()
    {
        $formattedData = $this->formatter->get();

        $this->assertEquals('123 Some Lane St', $formattedData['contact_info']['address_one']);

        $this->assertEquals(
            '05/20/1989',
            $formattedData['date_of_birth']
        );

    }
}
