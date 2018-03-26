<?php

use Carbon\Carbon;
use CollabCorp\Formatter\Formatter;
use CollabCorp\Formatter\Tests\TestCase;

class StringConverterTest extends TestCase
{

    public function setUp()
    {
        $this->formatter = new Formatter('This is a test string! 123 test foobar!');

    }
    /**
     * @test
     */
    public function startMethod()
    {
        $startWith ='Im a prefix that is added: original sentence->';
        $this->assertEquals($startWith.$this->formatter->get(), $this->formatter->start($startWith)->get());

    }

    /**
     * @test
     */
    public function ssnMethod()
    {
        $this->formatter->setValue('123456789');
        $this->assertEquals('123-45-6789', $this->formatter->ssn()->get());
    }

    /**
     * @test
     */
    public function phoneMethod()
    {
        $this->formatter->setValue('1234567890');
        $this->assertEquals('(123)456-7890', $this->formatter->phone()->get());
    }

    /**
     * @test
     */
    public function truncateMethod()
    {
        $this->assertEquals('This is a test string', $this->formatter->truncate(18)->get());
    }



    /**
     * @test
     */
    public function finishMethod()
    {
        $endWith ="<--original string. I was added at the end only if i didnt exist:D";
        $this->assertEquals($this->formatter->get().$endWith, $this->formatter->finish($endWith)->get());
    }


    /**
     * @test
     */
    public function beforeMethod()
    {
        $before ="123 test";
        $this->assertEquals('This is a test string! ', $this->formatter->before($before)->get());
    }


    /**
     * @test
     */
    public function afterMethod()
    {
        $after ="This is a test string! ";
        $this->assertEquals('123 test foobar!', $this->formatter->after($after)->get());
    }

    /**
     * @test
     */
    public function prefixMethod()
    {
        $prefix ="$";
        $this->formatter->setValue("500.75");
        $this->assertEquals('$500.75', $this->formatter->prefix($prefix)->get());
    }

    /**
     * @test
     */
    public function suffixMethod()
    {
        $suffix ="%";
        $this->formatter->setValue("50");
        $this->assertEquals('50%', $this->formatter->suffix($suffix)->get());
    }

    /**
     * @test
     */
    public function camelCaseMethod()
    {
        $this->formatter->setValue("foo bar");

        $this->assertEquals('fooBar', $this->formatter->camelCase()->get());
    }
    /**
     * @test
     */
    public function kebabCaseMethod()
    {
        $this->formatter->setValue("foo bar");
        $this->assertEquals('foo-bar', $this->formatter->kebabCase()->get());
    }


    /**
     * @test
     */
    public function snakeCaseMethod()
    {
        $this->formatter->setValue("foo bar");
        $this->assertEquals('foo_bar', $this->formatter->snakeCase()->get());
    }

    /**
     * @test
     */
    public function titleCaseMethod()
    {
        $this->formatter->setValue("foo bar");
        $this->assertEquals('Foo Bar', $this->formatter->titleCase()->get());
    }

    /**
     * @test
     */
    public function slugMethod()
    {
        $this->formatter->setValue("foo bar");
        $this->assertEquals('foo-bar', $this->formatter->slug()->get());
    }

    /**
     * @test
     */
    public function studlyCaseMethod()
    {
        $this->formatter->setValue("foo bar");
        $this->assertEquals('FooBar', $this->formatter->studlyCase()->get());
    }

    /**
     * @test
     */
    public function pluralMethod()
    {
        $this->formatter->setValue("child");
        $this->assertEquals('children', $this->formatter->plural()->get());
    }

    /**
     * @test
     */
    public function limitMethod()
    {
        $this->formatter->setValue("children");
        $this->assertEquals('child', $this->formatter->limit(5)->get());
    }

    /**
     * @test
     */
    public function replaceMethod()
    {
        $this->formatter->setValue("i will be a string that says");
        $this->assertEquals('foobar', $this->formatter->replace("i will be a string that says", "foobar")->get());
    }
    /**
    * @test
    */
    public function onlyNumbersMethod()
    {
        $this->formatter->setValue("sdfdsfsdf123");
        $this->assertEquals('123', $this->formatter->onlyNumbers()->get());
    }

    /**
     * @test
     */
    public function onlyLettersMethod()
    {
        $this->formatter->setValue("test*(&*#(*$&123");
        $this->assertEquals('test', $this->formatter->onlyLetters()->get());
    }
    /**
     * @test
     */
    public function alphaNumericMethod()
    {
        $this->formatter->setValue("test*(&*#(*$&123  ");
        $this->assertEquals('test123', $this->formatter->onlyAlphaNumeric()->get());
        //allow spaces
        $this->formatter->setValue("test*(&*#(*$&123  ");
        $this->assertEquals('test123  ', $this->formatter->onlyAlphaNumeric(true)->get());
    }

    /**
     * @test
     */
    public function trimMethod()
    {
        $this->formatter->setValue('$$$$moneyz$$$$$');
        $this->assertEquals('moneyz', $this->formatter->trim("$")->get());
    }
    /**
     * @test
     */
    public function rTrimMethod()
    {
        $this->formatter->setValue('$$$$moneyz$$$$$');
        $this->assertEquals('$$$$moneyz', $this->formatter->rtrim("$")->get());
    }
    /**
     * @test
     */
    public function lTrimMethod()
    {
        $this->formatter->setValue('$$$$moneyz$$$$$');
        $this->assertEquals('moneyz$$$$$', $this->formatter->ltrim("$")->get());
    }
    /**
     * @test
     */
    public function insertEveryMethod()
    {
        $this->formatter->setValue('1234567890123456');
        $this->assertEquals('1234 5678 9012 3456', $this->formatter->insertEvery(4, " ")->rtrim()->get());
    }
}
