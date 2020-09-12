<?php

namespace CollabCorp\Formatter\Tests;

use CollabCorp\Formatter\DataFormatter;
use InvalidArgumentException;

class DataFormatterTest extends TestCase
{
    public function setUp(): void
    {
        $this->data = [
          'first_name'=>'    jim    ',
          'last_name'=>'   thompson',
          'date_of_birth'=>"2018-04-01",
          'password'=>'abcdefgh12345',
          'favorite_number'=>"24",
          'favorite_date'=>null,
          'get_notifications'=> true,
          'contact_info'=>[
              'address_one'=>'123 some lane street',
              'home_phone'=>'1234567890',
              'cell_phone'=>'1234567890',
              'apartment_number'=>'12',
              'email'=>'email@example.com',
          ]
        ];

    }

    /** @test */
    public function it_calls_callables_on_data()
    {
        $formatter = (new DataFormatter($this->data, [
            'first_name'=>'trim|ucfirst',
            'favorite_number'=>'intval'
        ]));

        $formattedData = $formatter->apply()->get();

        $this->assertEquals("Jim", $formattedData['first_name']);
        $this->assertEquals(24, $formattedData['favorite_number']);
        $this->assertNotEquals($formattedData['first_name'], $this->data["first_name"]);
    }
    /** @test */
    public function it_calls_callables_on_data_only_if_specified()
    {
        $formatter = (new DataFormatter($this->data, []));

        $formattedData = $formatter->apply()->get();
        //nothing should change
        $this->assertEquals($this->data, $formattedData);
    }

    /** @test */
    public function it_throws_exceptions_when_callable_cannot_be_called()
    {
        $this->expectException(InvalidArgumentException::class);

        $formatter = (new DataFormatter($this->data, [
            'first_name'=>'idont_exist'
        ]));

        $formattedData = $formatter->apply()->get();
    }

    /** @test */
    public function callables_can_specify_value_order()
    {
        $formatter = (new DataFormatter($this->data, [
            'password'=>'trim|preg_replace:/[^0-9]/,,:value:'
        ]));

        $formattedData = $formatter->apply()->get();
        $this->assertEquals("12345", $formattedData['password']);
        $this->assertNotEquals($formattedData['password'], $this->data['password']);
    }

    /** @test */
    public function it_can_specify_optional_callables_on_blank_input()
    {
        //assert as first
        $formatter = (new DataFormatter($this->data, [
            'favorite_date'=>'?|to_carbon|.format:m/d/Y'
        ]));

        $formattedData = $formatter->apply()->get();

        $this->assertEquals($this->data['favorite_date'], $formattedData['favorite_date']);
        $this->assertNotEquals("04/01/2018", $formattedData['favorite_date']);

        //assert at any position in the list of callables
        $formatter = (new DataFormatter($this->data, [
            'favorite_date'=>['to_carbon', function(){
                return null;
            }, '?', '.format:m/d/Y']
        ]));

        $formattedData = $formatter->apply()->get();

        $this->assertEquals(null, $formattedData['favorite_date']);

    }

    /** @test */
    public function it_can_process_callbacks()
    {
        $formatter = (new DataFormatter($this->data, [
            'get_notifications'=> function($value){
                return $value === true ? 'Yes': 'No';
            },
        ]));

        $formattedData = $formatter->apply()->get();

        $this->assertEquals("Yes", $formattedData['get_notifications']);

        $this->assertNotEquals($this->data['get_notifications'], $formattedData['get_notifications']);
    }

    /** @test */
    public function it_can_delegate_to_underlying_objects()
    {
        $formatter = (new DataFormatter($this->data, [
            'date_of_birth'=>'trim|to_carbon|.addDays:2|.format:m/d/Y'
        ]));

        $formattedData = $formatter->apply()->get();

        $this->assertNotEquals($formattedData['date_of_birth'], $this->data['date_of_birth']);

        $this->assertEquals('04/02/2018', $formattedData['date_of_birth']);
    }
}
