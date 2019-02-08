<?php

namespace CollabCorp\LaravelInputFormatter\Tests;

use CollabCorp\LaravelInputFormatter\DataFormatter;
use CollabCorp\LaravelInputFormatter\Tests\TestCase;

class DataFormatterTest extends TestCase
{
    public function setUp()
    {
        $this->formatter = new DataFormatter('This is a test string! 123 test foobar!');
    }
    /** @test */
    public function itCanMassConvertAndCanConvertWithPatterns()
    {
        $data = [
            'foo'=>'jksdalfs8(&$##',
            'something_foo'=>'jksdalfs8(&$##',
            'something_bar'=>'   something ',
            'baz_something'=>2,
            'same'=>'I wont change',
            'array'=>['faksd1', 'sladfjl2', 'sadjs3']
        ];

        $data = DataFormatter::convert($data, [
            '*foo*'=>'onlyNumbers',
            '*bar'=>'trim|suffix:yay',
            'baz*'=>'add:2',
            'array'=>'onlyNumbers'
        ]);

        $this->assertEquals($data['foo'], 8);
        $this->assertEquals($data['something_foo'], 8);
        $this->assertEquals($data['something_bar'], "somethingyay");
        $this->assertEquals($data['baz_something'], 4);
        $this->assertEquals($data['same'], 'I wont change');
        $this->assertEquals($data['array'], [1,2,3]);
    }
    /** @test */
    public function itCanBailProcessingIfValueIsEmpty()
    {
        $data = [
            'date'=>null
        ];

        $data = DataFormatter::convert($data, [
            'date'=>'bailIfEmpty|toCarbon',
        ]);

        $this->assertEquals($data['date'], null);
    }
    /** @test */
    public function itAppliesFormattersRecursivelyOnArrays()
    {
        $formatter = (new DataFormatter(['123something', ["foo123","bar456",'baz678']]))->onlyNumbers();
        $this->assertEquals(['123',['123','456', '678']], $formatter->get());
    }
    /** @test */
    public function itCanMassConvertUsingClosuresAndFormattableObjects()
    {
        $data = [
            'foo'=>'kalsdf23'
        ];

        $data = DataFormatter::convert($data, [
            'foo'=>['onlyNumbers','add:2', function ($value) {
                return $value + 2;
            }, TesterFormatClass::class], //class just adds 4 to value
        ]);

        $this->assertEquals($data['foo'], 31);
    }
    /** @test */
    public function itIsMacroable()
    {
        DataFormatter::macro('hello', function () {
            return $this->setValue('hello '.$this->value);
        });

        $this->assertEquals("hello sergio", (new DataFormatter("sergio"))->hello()->get());


        DataFormatter::macro('goodbye', function () {
            return $this->setValue('goodbye '.$this->value);
        });

        $this->assertEquals("goodbye sergio", (new DataFormatter("sergio"))->goodbye()->get());
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
    public function singularMethod()
    {
        $this->formatter->setValue("children");
        $this->assertEquals('child', $this->formatter->singular()->get());
    }

    /**
     * @test
     */
    public function replaceMethod()
    {
        $this->formatter->setValue("i will be a string that says");
        $this->assertEquals('foobar', $this->formatter->replace("i will be a string that says", "foobar")->get());
    }
}
