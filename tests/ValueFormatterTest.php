<?php

namespace CollabCorp\Formatter\Tests;

use CollabCorp\Formatter\Support\ValueFormatter;
use InvalidArgumentException;

class ValueFormatterTest extends TestCase
{

    /** @test */
    public function it_calls_callables_on_values()
    {
        $formatter = (new ValueFormatter("   uncle bob  ", [
            'trim',
            'ucwords',
        ]));

        $formattedValue = $formatter->apply()->get();

        $this->assertEquals("Uncle Bob", $formattedValue);
    }
    /** @test */
    public function it_calls_callables_on_data_only_if_specified()
    {
        $formatter = (new ValueFormatter("unchanged", []));

        $formattedValue = $formatter->apply()->get();
        //nothing should change
        $this->assertEquals("unchanged", $formattedValue);
    }

    /** @test */
    public function it_throws_exceptions_when_callable_cannot_be_called()
    {
        $this->expectException(InvalidArgumentException::class);

        $formatter = (new ValueFormatter("Some Value", [
            'idont_exist'
        ]));

        $formattedData = $formatter->apply()->get();
    }

    /** @test */
    public function callables_can_specify_value_order()
    {
        $formatter = (
            new ValueFormatter(
                $original = "   12345Abc",
                'trim|preg_replace:/[^0-9]/,,:value:'
            )
        );

        $password = $formatter->apply()->get();

        $this->assertEquals("12345", $password);

        $this->assertNotEquals($password, $original);
    }

    // /** @test */
    // public function it_can_specify_optional_callables_on_blank_input()
    // {
    //     //assert as first
    //     $formatter = (new DataFormatter($this->data, [
    //         'favorite_date'=>'?|to_carbon|.format:m/d/Y'
    //     ]));

    //     $formattedData = $formatter->apply()->get();

    //     $this->assertEquals($this->data['favorite_date'], $formattedData['favorite_date']);
    //     $this->assertNotEquals("04/01/2018", $formattedData['favorite_date']);

    //     //assert at any position in the list of callables
    //     $formatter = (new DataFormatter($this->data, [
    //         'favorite_date'=>['to_carbon', function(){
    //             return null;
    //         }, '?', '.format:m/d/Y']
    //     ]));

    //     $formattedData = $formatter->apply()->get();

    //     $this->assertEquals(null, $formattedData['favorite_date']);

    // }

    // /** @test */
    // public function it_can_process_callbacks()
    // {
    //     $formatter = (new DataFormatter($this->data, [
    //         'get_notifications'=> function($value){
    //             return $value === true ? 'Yes': 'No';
    //         },
    //     ]));

    //     $formattedData = $formatter->apply()->get();

    //     $this->assertEquals("Yes", $formattedData['get_notifications']);

    //     $this->assertNotEquals($this->data['get_notifications'], $formattedData['get_notifications']);
    // }

    // /** @test */
    // public function it_can_delegate_to_underlying_objects()
    // {
    //     $formatter = (new DataFormatter($this->data, [
    //         'date_of_birth'=>'trim|to_carbon|.addDays:1|.format:m/d/Y'
    //     ]));

    //     $formattedData = $formatter->apply()->get();

    //     $this->assertNotEquals($formattedData['date_of_birth'], $this->data['date_of_birth']);

    //     $this->assertEquals('05/25/2020', $formattedData['date_of_birth']);
    // }
}
