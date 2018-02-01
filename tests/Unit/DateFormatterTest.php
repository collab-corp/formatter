<?php

use Carbon\Carbon;
use CollabCorp\Formatter\Formatter;
use CollabCorp\Formatter\Tests\TestCase;

class FormatterTest extends TestCase
{
    public function setUp()
    {
        $this->formatter = new Formatter('12/22/2030');
        $this->formatter = $this->formatter->toCarbon();
    }
  

    /**
     * @test
     */
    public function formatterToCarbonMethodReturnsCarbonInstance()
    {
        $this->assertInstanceOf(Carbon::class, (new Formatter('12/22/2030'))->toCarbon()->get());
    }
    /**
     * @test
     */
    public function formatterCanChangeDateFormat()
    {
        $this->formatter = $this->formatter->format('F d, Y');
        $this->assertEquals('December 22, 2030', $this->formatter->get());
    }
    /**
     * @test
     */
    public function formatterCanChangeCarbonTimezone()
    {
        $this->formatter = $this->formatter->setTimeZone('America/Toronto');

        $this->assertEquals('America/Toronto', $this->formatter->get()->tzName);
    }
    /**
     * @test
     */
    public function formatterCanCallCarbonAddMethods()
    {
        $this->assertEquals('2030-12-22 00:00:02', $this->formatter->addSeconds(2)->get()->toDateTimeString());
        $this->assertEquals('2030-12-22 00:02:02', $this->formatter->addMinutes(2)->get()->toDateTimeString());
        $this->assertEquals('2030-12-22 02:02:02', $this->formatter->addHours(2)->get()->toDateTimeString());

        $this->assertEquals('2030-12-24', $this->formatter->addDays(2)->get()->toDateString());
        $this->assertEquals('2031-01-07', $this->formatter->addWeeks(2)->get()->toDateString());
        $this->assertEquals('2031-03-07', $this->formatter->addMonths(2)->get()->toDateString());
        $this->assertEquals('2033-03-07', $this->formatter->addYears(2)->get()->toDateString());
    }

    /**
     * @test
     */
    public function formatterCanCallCarbonSubMethods()
    {
        $this->formatter = (new Formatter('2030-12-22 03:40:02'))->toCarbon();

        $this->assertEquals('2030-12-22 03:40:00', $this->formatter->subSeconds(2)->get()->toDateTimeString());
        $this->assertEquals('2030-12-22 03:38:00', $this->formatter->subMinutes(2)->get()->toDateTimeString());
        $this->assertEquals('2030-12-22 01:38:00', $this->formatter->subHours(2)->get()->toDateTimeString());

        $this->assertEquals('2030-12-20', $this->formatter->subDays(2)->get()->toDateString());
        $this->assertEquals('2030-12-06', $this->formatter->subWeeks(2)->get()->toDateString());
        $this->assertEquals('2030-10-06', $this->formatter->subMonths(2)->get()->toDateString());
        $this->assertEquals('2028-10-06', $this->formatter->subYears(2)->get()->toDateString());
    }
}
