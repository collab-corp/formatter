<?php

use Carbon\Carbon;
use CollabCorp\Formatter\Formatter;
use CollabCorp\Formatter\Tests\TestCase;

class MathConverterTest extends TestCase
{
    public function setUp()
    {
        $this->formatter = new Formatter(20);
    }

    /**
     * @test
     */
    public function addMethod()
    {
        $this->assertEquals(22, $this->formatter->add(2)->get());
    }


    /**
     * @test
     */
    public function subtractMethod()
    {
        $this->assertEquals(18, $this->formatter->subtract(2)->get());
    }

    /**
     * @test
     */
    public function divideMethod()
    {
        $this->assertEquals(10, $this->formatter->divide(2)->get());
    }


    /**
     * @test
     */
    public function multiplyMethod()
    {
        $this->assertEquals(40, $this->formatter->multiply(2)->get());
    }
    /**
     * @test
     */
    public function powerMethod()
    {
        $this->assertEquals(400, $this->formatter->power(2)->get());
    }

    /**
    * @test
    */
    public function roundToMethod()
    {
        $this->assertEquals(20.000, $this->formatter->roundTo(3)->get());
    }

    /**
     * @test
     */
    public function percentageMethod()
    {
        //test scaling
        $this->assertEquals(0.20, $this->formatter->percentage()->get());

        //reset the value
        $this->formatter->setValue(10);
    }

    /**
     * @test
     */
    public function multipleCalculations()
    {
        $this->assertEquals(9, $this->formatter->add(2)->subtract(4)->divide(2)->get());
    }
}
