<?php

namespace SymfonyRollbarBundle\Tests\Provider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyRollbarBundle\Provider\Api\Filter;

/**
 * Class FilterTest
 *
 * @package SymfonyRollbarBundle\Tests\Provider
 */
class FilterTest extends KernelTestCase
{
    /**
     * @dataProvider generatorLength
     *
     * @param string $str
     * @param array  $options
     * @param string $expected
     */
    public function testLength($str, $options, $expected)
    {
        $result = Filter::process($str, Filter\Length::class, $options);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function generatorLength()
    {
        return [
            [null, ['max' => 5], null],
            ['', ['max' => 5], ''],
            ['wor', ['max' => 5], 'wor'],
            ['world', ['max' => 5], 'world'],
            ['world-truncated', ['max' => 5], 'world'],
        ];
    }
}
