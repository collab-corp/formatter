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

    /** @test */
    public function it_can_specify_optional_callables_on_blank_input()
    {
        //assert as first
        $formatter = (new ValueFormatter(null, '?|to_carbon|.format:m/d/Y'));

        $value = $formatter->apply()->get();

        $this->assertEquals(null, $value);

    }

    /** @test */
    public function it_can_process_callbacks()
    {
        $formatter = (new ValueFormatter(true, [
            'get_notifications'=> function($value){
                return $value === true ? 'Yes': 'No';
            },
        ]));

        $value = $formatter->apply()->get();

        $this->assertEquals("Yes", $value);
    }

    /** @test */
    public function it_can_delegate_to_underlying_objects()
    {
        $formatter = (new ValueFormatter('   2020-05-24  ', [
            'trim',
            'to_carbon',
            '.addDays:1',
            '.format:m/d/Y'
        ]));

        $value = $formatter->apply()->get();

        $this->assertEquals('05/25/2020', $value);
    }
}
