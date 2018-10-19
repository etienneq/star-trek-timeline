<?php
namespace Tests\EtienneQ\StarTrekTimeline\Data;

use EtienneQ\StarTrekTimeline\Data\Item;
use EtienneQ\StarTrekTimeline\Data\ItemFactory;
use EtienneQ\StarTrekTimeline\Data\MetaData;
use PHPUnit\Framework\TestCase;

/**
 * @covers \EtienneQ\StarTrekTimeline\Data\ItemFactory
 */
class ItemFactoryTest extends TestCase
{
    /**
     * @dataProvider dataProviderItemId
     */
    public function testCreateItemShouldGenerateValidItemId(string $number, string $expectedItemId)
    {
        $metaData = new MetaData('packageId');
        $attributes = [
            'number' => $number,
            'title' => 'Crazy Episode',
            'startDate' => '2370',
            'startStardate' => 41000,
            'endStardate' => 41001,
        ];
        
        $factory = new ItemFactory();
        
        $item = $factory->createItem($attributes, $metaData);
        
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals($expectedItemId, $item->getId());
    }
    
    public function dataProviderItemId():array
    {
        return [
            ['1x07', 'packageId-1x07'],
            ['--', 'packageId-CE'],
            ['', 'packageId-CE'],
        ];
    }
}
