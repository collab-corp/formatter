<?php

namespace CollabCorp\Formatter\Tests\Feature;

use Carbon\Carbon;
use CollabCorp\Formatter\Formatter;
use CollabCorp\Formatter\MathFormatter;
use CollabCorp\Formatter\StringFormatter;
use CollabCorp\Formatter\Tests\TestCase;

/**
 * This testcase will serve as a general purpose for tests,
 * that doesn't quite fit into a separate class,
 * or is a general feature of the Formatter object.
 */
class FormatterTest extends TestCase
{
    /** @test */
    public function itGetsTheResultWhenCastToAString()
    {
        $text = Formatter::create("hello world");
        $number = Formatter::create(1);

        $this->assertEquals("hello world", (string)$text);
        $this->assertEquals("1", (string)$number);
    }

    /** @test */
    public function itCanProcessesMultipleValuesOnInstantiation()
    {
        $formatter = (new Formatter(['123something', ["foo123","bar456",'baz678']]))->onlyNumbers();
        $this->assertEquals(['123',['123','456', '678']], $formatter->get());
    }
    /** @test */
    public function it_is_macroable()
    {
        Formatter::macro('capitalize', function () {
            return $this->setValue(strtoupper($this->value));
        });

        $this->assertEquals("SERGIO", (new Formatter("sergio"))->capitalize()->get());

        Formatter::macro('hello', function () {
            return $this->setValue("hello {$this->value}");
        });

        $this->assertEquals("hello sergio", (new Formatter("sergio"))->hello()->get());
    }
}
