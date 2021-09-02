<?php

namespace OfxParserTest;

use PHPUnit\Framework\TestCase;
use OfxParser\Utils;

/**
 * Fake class for DateTime callback.
 */
class MyDateTime extends \DateTime { }

/**
 * @covers OfxParser\Utils
 */
class UtilsTest extends TestCase
{
    public function amountConversionProvider()
    {
        return [
            '1000.00' => ['1000.00', 1000.0],
            '1000,00' => ['1000,00', 1000.0],
            '1,000.00' => ['1,000.00', 1000.0],
            '1.000,00' => ['1.000,00', 1000.0],
            '-1000.00' => ['-1000.00', -1000.0],
            '-1000,00' => ['-1000,00', -1000.0],
            '-1,000.00' => ['-1,000.00', -1000.0],
            '-1.000,00' => ['-1.000,00', -1000.0],
            '1' => ['1', 1.0],
            '10' => ['10', 10.0],
            '100' => ['100', 1.0], // @todo this is weird behaviour, should not really expect this
            '+1' => ['+1', 1.0],
            '+10' => ['+10', 10.0],
            '+1000.00' => ['+1000.00', 1000.0],
            '+1000,00' => ['+1000,00', 1000.0],
            '+1,000.00' => ['+1,000.00', 1000.0],
            '+1.000,00' => ['+1.000,00', 1000.0],
        ];
    }

    /**
     * @param string $input
     * @param float $output
     * @dataProvider amountConversionProvider
     */
    public function testCreateAmountFromStr($input, $output)
    {
        $actual = Utils::createAmountFromStr($input);
        self::assertSame($output, $actual);
    }

    public function testCreateDateTimeFromOFXDateFormats()
    {
        // October 5, 2008, at 1:22 and 124 milliseconds pm, Easter Standard Time
        $expectedDateTime = new \DateTime('2008-10-05 13:22:00');

        // Test OFX Date Format YYYYMMDDHHMMSS.XXX[gmt offset:tz name]
        $dateTime = Utils::createDateTimeFromStr('20081005082200.124[-5:EST]');
        self::assertEquals($expectedDateTime->getTimestamp(), $dateTime->getTimestamp());

        // Test OFX Date Format YYYYMMDDHHMMSS.XXX[gmt offset:tz name]
        $dateTime = Utils::createDateTimeFromStr('20081005102200.124[-3:GMT]');
        self::assertEquals($expectedDateTime->getTimestamp(), $dateTime->getTimestamp());

        // Test OFX Date Format YYYYMMDDHHMMSS.XXX[gmt offset:tz name]
        $dateTime = Utils::createDateTimeFromStr('20081005132200.124[-0:UTC]');
        self::assertEquals($expectedDateTime->getTimestamp(), $dateTime->getTimestamp());

        // Test YYYYMMDD
        $dateTime = Utils::createDateTimeFromStr('20081005');
        self::assertEquals($expectedDateTime->format('Y-m-d'), $dateTime->format('Y-m-d'));

        // Test YYYYMMDDHHMMSS
        $dateTime = Utils::createDateTimeFromStr('20081005132200');
        self::assertEquals($expectedDateTime->getTimestamp(), $dateTime->getTimestamp());

        // Test YYYYMMDDHHMMSS.XXX
        $dateTime = Utils::createDateTimeFromStr('20081005132200.124');
        self::assertEquals($expectedDateTime->getTimestamp(), $dateTime->getTimestamp());

        // Test empty datetime
        $dateTime = Utils::createDateTimeFromStr('');
        self::assertEquals(null, $dateTime);

        // Test DateTime factory callback
        Utils::$fnDateTimeFactory = function($format) { return new MyDateTime($format); };
        $dateTime = Utils::createDateTimeFromStr('20081005');
        self::assertEquals($expectedDateTime->format('Y-m-d'), $dateTime->format('Y-m-d'));
        self::assertEquals('OfxParserTest\\MyDateTime', get_class($dateTime));
        Utils::$fnDateTimeFactory = null;
    }
}
