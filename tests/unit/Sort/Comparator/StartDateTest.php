<?php
namespace Tests\EtienneQ\StarTrekTimeline\Sort\Comparator;

use PHPUnit\Framework\TestCase;
use EtienneQ\StarTrekTimeline\Data\Item;
use EtienneQ\Stardate\Calculator;
use EtienneQ\StarTrekTimeline\Sort\Comparator\StartDate;

/**
 * @covers \EtienneQ\StarTrekTimeline\Sort\Comparator\StartDate
 */
class StartDateTest extends TestCase
{
    /**
     * @dataProvider dataProviderStartDates
     */
    public function testCompare(string $date1, string $date2, int $expectedResult) {
        $calculator = $this->prophesize(Calculator::class);
        $item1 = new Item('1', 'title', $date1, $calculator->reveal());
        $item2 = new Item('2', 'title', $date2, $calculator->reveal());
        
        $comparator = new StartDate();
        
        $result = $comparator->compare($item1, $item2);
        
        $this->assertInternalType('int', $result);
        $this->assertEquals($expectedResult, $result);
    }
    
    public function dataProviderStartDates():array
    {
        return [
            // both Y
            ['-1', '1', -1],
            ['1', '-1', 1],
            ['-1', '-1', 0],
            ['1', '1', 0],
            ['0', '0', 0],
            ['1', '0', 1],
            ['0', '1', -1],
            
            ['~-1', '1', -1],
            ['~1', '-1', 1],
            ['~1', '1', 0],
            ['~0', '0', 0],
            ['~1', '0', 1],
            ['~0', '1', -1],
            
            ['~-1', '~1', -1],
            ['~1', '~-1', 1],
            ['~1', '~1', 0],
            ['~0', '~0', 0],
            ['~1', '~0', 1],
            ['~0', '~1', -1],
            
            ['-1', '~1', -1],
            ['1', '~-1', 1],
            ['1', '~1', 0],
            ['0', '~0', 0],
            ['1', '~0', 1],
            ['0', '~1', -1],

            // both Y-m
            
            // both Y-m-d
            
            
            // Y-m vs. Y
            
            // Y vs. Y-m
            
            
            
            // Y-m-d vs. Y
            
            // Y vs. Y-m-d
            
            
            
            // Y-m-d vs. Y-m
            
            // Y-m vs. Y-m-d
        ];
    }
}
