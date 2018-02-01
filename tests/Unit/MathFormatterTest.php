<?php

use Carbon\Carbon;
use CollabCorp\Formatter\Formatter;
use CollabCorp\Formatter\Tests\TestCase;

class MathFormatterTest extends TestCase
{
    public function setUp()
    {
        $this->formatter = new Formatter(20);
    }

    /**
     * @test
     */
    public function mathFormatterCanAddNumberToValue()
    {
        $this->assertEquals(22, $this->formatter->add(2)->get());
        //test the scaling
        $this->assertEquals(24.00, $this->formatter->add(2, 2)->get());
    }


    /**
     * @test
     */
    public function mathFormatterCanSubtractNumberToValue()
    {
        $this->assertEquals(18, $this->formatter->subtract(2)->get());

        //test scaling
        $this->assertEquals(16.00, $this->formatter->subtract(2, 2)->get());
    }

    /**
     * @test
     */
    public function mathFormatterCanDivideValueByANumber()
    {
        $this->assertEquals(10, $this->formatter->divide(2)->get());
        //test the scaling
        $this->assertEquals(5.00, $this->formatter->divide(2, 2)->get());
    }


    /**
     * @test
     */
    public function mathFormatterCanMultiplyValueByANumber()
    {
        $this->assertEquals(40, $this->formatter->multiply(2)->get());

        //test scaling
        $this->assertEquals(80.00, $this->formatter->multiply(2, 2)->get());
    }
    /**
     * @test
     */
    public function mathFormatterCanRaiseValueToPower()
    {
        $this->assertEquals(400, $this->formatter->power(2)->get());

        //test scaling
        $this->assertEquals(160000.00, $this->formatter->power(2, 2)->get());
    }

    /**
    * @test
    */
    public function mathFormatterCanFormatDecimalPlaces()
    {
        $this->assertEquals(20.000, $this->formatter->decimals(3)->get());
    }

    /**
     * @test
     */
    public function mathFormatterCanConvertValueToDecimalPercent()
    {
        //test scaling
        $this->assertEquals(0.20, $this->formatter->percentage()->get());

        //reset the value
        $this->formatter->setValue(10);
        //scaling 3 places
        $this->assertEquals(0.100, $this->formatter->percentage(3)->get());
    }

    /**
     * @test
     */
    public function mathFormatterCanRunMultipleCalculations()
    {
        $this->assertEquals(9, $this->formatter->add(2)->subtract(4)->divide(2)->get());
    }
}
