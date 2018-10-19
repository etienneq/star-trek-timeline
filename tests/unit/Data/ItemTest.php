<?php
namespace Tests\EtienneQ\StarTrekTimeline\Data;

use EtienneQ\StarTrekTimeline\Data\ItemException;
use EtienneQ\StarTrekTimeline\Data\Item;
use EtienneQ\StarTrekTimeline\Data\MetaData;
use PHPUnit\Framework\TestCase;
use EtienneQ\Stardate\Calculator;

/**
 * @covers \EtienneQ\StarTrekTimeline\Data\Item
 */
class ItemTest extends TestCase
{
    /**
     * @var Calculator
     */
    protected static $calculator;
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        self::$calculator = new Calculator();
    }
    
    public function testConstructWithEmptyTitleShouldThrowException()
    {
        $this->expectException(ItemException::class);
        
        new Item('id', '', 'startDate', self::$calculator);
    }
    
    public function testConstructWithEmptyStartDateShouldThrowException()
    {
        $this->expectException(ItemException::class);
        
        new Item('id', 'title', '', self::$calculator);
    }
    
    public function testConstructWithInvalidStartDateShouldThrowException()
    {
        $this->expectException(ItemException::class);
        
        new Item('id', 'title', 'invalid startDate', self::$calculator);
    }
    
    /**
     * @dataProvider dateProviderValidDates
     */
    public function testConstructWithValidStartDateShouldInstantiateItemProperly(string $startDate)
    {
        $expectedId = 'id';
        $expectedTitle = 'title';
        $item = new Item($expectedId, $expectedTitle, $startDate, self::$calculator);
        
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals($expectedId, $item->getId());
        $this->assertEquals($expectedTitle, $item->getTitle());
        $this->assertEquals($startDate, $item->getStartDate());
    }
    
    public function dateProviderValidDates():array
    {
        return [
            ['2364'],
            ['2364-05'],
            ['2364-05-17'],
            ['-2364'],
            ['-2364-05'],
            ['-2364-05-17'],
            ['~2364'],
            ['~-2364'],
        ];
    }
    
    public function testGetIdShouldReturnId()
    {
        $expectedId = 'id';
        $item = new Item($expectedId, 'title', '2364', self::$calculator);
        
        $id = $item->getId();
        
        $this->assertEquals($expectedId, $id);
    }
    
    public function testSetGetMetaData()
    {
        $item = new Item('id', 'title', '2364', self::$calculator);
        $expectedPackageId = 'packageId';
        $item->setMetaData(new MetaData($expectedPackageId));
        
        $metaData = $item->getMetaData();
        
        $this->assertInstanceOf(MetaData::class, $metaData);
        $this->assertEquals($expectedPackageId, $metaData->getId());
    }
    
    public function testSetGetParent()
    {
        $expectedPubDate = '2018-01-20';
        $expectedParentId = 'parentId';
        $expectedParent = new Item($expectedParentId, 'title', '2364', self::$calculator);
        $expectedParent->publicationDate = $expectedPubDate;
        
        $item = new Item('id', 'title', '2364', self::$calculator);
        $item->setParent($expectedParent);
        
        $parent = $item->getParent();
        
        $this->assertInstanceOf(Item::class, $parent);
        $this->assertEquals($expectedParentId, $parent->getId());
        $this->assertEquals($expectedPubDate, $item->publicationDate);
    }
    
    public function testSetEndDateWithInvalidDateShouldThrowException()
    {
        $this->expectException(ItemException::class);
        
        $item = new Item('id', 'title', '2364', self::$calculator);
        
        $item->setEndDate('invalid endDate');
    }
    
    /**
     * @dataProvider dateProviderValidDates
     */
    public function testSetEndDateWithValidDateShouldSetEndDate(string $date)
    {
        $item = new Item('id', 'title', '2364', self::$calculator);
        $item->setEndDate($date);
        
        $this->assertEquals($date, $item->getEndDate());
    }
    
    /**
     * @dataProvider dataProviderInvalidStardates
     */
    public function testSetStartStardateWithInvalidStardateShouldThrowException($stardate, string $exceptionClass)
    {
        $this->expectException($exceptionClass);
        
        $item = new Item('id', 'title', '2364', self::$calculator);
        
        $item->setStartStardate($stardate);
    }
    
    /**
     * @dataProvider dataProviderInvalidStardates
     */
    public function testSetEndStardateWithInvalidStardateShouldThrowException($stardate, string $exceptionClass)
    {
        $this->expectException($exceptionClass);
        
        $item = new Item('id', 'title', '2364', self::$calculator);
        
        $item->setEndStardate($stardate);
    }
    
    public function dataProviderInvalidStardates():array
    {
        return [
            [-1000, ItemException::class],
            [-0.1, ItemException::class],
            ['invalid stardate', \TypeError::class],
        ];
    }
    
    /**
     * @dataProvider dataProviderValidStartStardates
     */
    public function testSetStartStardateWithValidStardateShouldSetStardateAndRecalculateStartDate(
        float $stardate,
        string $oldStartDate,
        string $expectedStartDate
    ) {
        $item = new Item('id', 'title', $oldStartDate, self::$calculator);
        
        $item->setStartStardate($stardate);
        
        $this->assertEquals($stardate, $item->getStartStardate());
        $this->assertEquals($expectedStartDate, $item->getStartDate());
    }
    
    public function dataProviderValidStartStardates():array
    {
        return [
            'empty stardate' => [0, '2367', '2367'],
            'not TNG-era stardate' => [9000, '2290', '2290'],
            [41000, '2364', '2364-01-01'],
        ];
    }
    
    /**
     * @dataProvider dataProviderValidEndStardates
     */
    public function testSetEndStardateWithValidStardateShouldSetStardateAndRecalculateEndDate(
        float $stardate,
        string $oldEndDate,
        string $expectedEndDate
        ) {
            $item = new Item('id', 'title', '2364', self::$calculator);
            $item->setEndDate($oldEndDate);

            $item->setendStardate($stardate);
            
            $this->assertEquals($stardate, $item->getEndStardate());
            $this->assertEquals($expectedEndDate, $item->getEndDate());
    }
    
    public function dataProviderValidEndStardates():array
    {
        $testCases = [
            'empty endDate' => [41000, '', ''],
        ];
        $testCases = array_merge($this->dataProviderValidStartStardates(), $testCases);
        return $testCases;
    }
    
    public function testGetStartEndStartDateWithoutSettingItShouldReturnNull()
    {
        $item = new Item('id', 'title', '2364', self::$calculator);
        
        $this->assertNull($item->getStartStardate());
        $this->assertNull($item->getEndStardate());
    }
    
    /**
     * @dataProvider dateProviderStartDateTngEra
     */
    public function testIsStartDateInTngStardateEra(string $startDate, bool $exptectedResult)
    {
        $item = new Item('id', 'title', $startDate, self::$calculator);
        
        $result = $item->isStartDateInTngStardateEra();
        
        $this->assertEquals($exptectedResult, $result);
    }
    
    public function dateProviderStartDateTngEra():array
    {
        return [
            ['-10000', false],
            ['0', false],
            ['2320', false],
            ['2320-02', false],
            ['2320-02-25', false],
            ['2322-12-31', false],
            ['2323', true],
            ['2323-01-01', true],
            ['2364', true],
            ['2364-05', true],
            ['2364-05-09', true],
            ['10000', true],
        ];
    }
}
