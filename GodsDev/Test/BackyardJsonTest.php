<?php

namespace GodsDev\Backyard\Test;

use GodsDev\Backyard\BackyardJson;
use GodsDev\Backyard\BackyardError;
use GodsDev\Backyard\BackyardHttp;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-06-25 at 17:57:37.
 */
class BackyardJsonTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var BackyardJson
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $backyardError = new BackyardError(array('logging_level' => 4));
        $this->object = new BackyardJson($backyardError, new BackyardHttp($backyardError));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        // no action
    }

    /**
     * @covers GodsDev\Backyard\BackyardJson::minifyJSON
     */
    public function testMinifyJSON()
    {
        $orig = '{"status": "1232", "text": "abc"}';
        $expected = '{"status":"1232","text":"abc"}';

        $this->assertEquals($expected, $this->object->minifyJSON($orig));
    }

    /**
     * @covers GodsDev\Backyard\BackyardJson::minifyJSON
     *
     * Logs something like: [30-Oct-2020 14:09:36] [fatal] [16] [/tmp/vendor/bin/phpunit] [anonymous@-] [0] [-]
     *     ERROR IN JSON: "status": "1233", "text": "abc"}
     */
    public function testMinifyJSONInvalid()
    {
        $orig = '"status": "1233", "text": "abc"}';
        $expected = '{"status": "500", "error": "Internal error"}';

        $this->assertEquals($expected, $this->object->minifyJSON($orig));
    }

    /**
     * @covers GodsDev\Backyard\BackyardJson::outputJSON
     * @todo   Implement testOutputJSON().
     */
    public function testOutputJSON()
    {
        $orig = '{"status": "1234", "text": "abc"}';
        $expected = '{"status":"1234","text":"abc"}';

        //$this->assertEquals($expected, $this->object->outputJSON($orig));
        $this->markTestSkipped('It would output: Cannot modify header information - headers already sent by ');
    }

    /**
     * @covers GodsDev\Backyard\BackyardJson::jsonCleanDecode
     */
    public function testJsonCleanDecode()
    {
        $orig = '{'
            . '"status": "1235", //some comment' . PHP_EOL
            . ' "text": "abc" //another comment' . PHP_EOL
            . '}';
        $expected = '{"status":"1235","text":"abc"}';

        $this->assertEquals($expected, json_encode($this->object->jsonCleanDecode($orig, true)));
    }

    /**
     * @covers GodsDev\Backyard\BackyardJson::getJsonAsArray
     */
    public function testGetJsonAsArray()
    {
        $url = 'https://raw.githubusercontent.com/GodsDev/backyard/master/src/js/dummy.json';
        $expected = array('alfa' => 'beta');

        $this->assertEquals($expected, $this->object->getJsonAsArray($url));
    }
}
