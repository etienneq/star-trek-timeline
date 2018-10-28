<?php
namespace Tests\EtienneQ\StarTrekTimeline\Data;

use EtienneQ\StarTrekTimeline\Data\MetaData;
use PHPUnit\Framework\TestCase;

/**
 * @covers \EtienneQ\StarTrekTimeline\Data\MetaData
 */
class MetaDataTest extends TestCase
{
    public function testGetIdShouldReturnId()
    {
        $expectedId = 'id';
        $metaData = new MetaData($expectedId);
        
        $this->assertEquals($expectedId, $metaData->getId());
    }
    
    /**
     * @dataProvider dataProviderPackageId
     */
    public function testisTngEraTvSeriesShouldReturnBool(string $id, bool $result)
    {
        $metaData = new MetaData($id);
        
        $this->assertEquals($result, $metaData->isTngEraTvSeries());
    }
    
    public function dataProviderPackageId():array
    {
        return [
            ['foo', false],
            ['tv/ent/season1', false],
            ['tv/tng/season0', false],
            ['foo/tng/season1', false],
            ['tv/tng/season1/foo', false],
            ['tv/tng/season1', true],
            ['tv/tng/season2', true],
            ['tv/tng/season3', true],
            ['tv/tng/season4', true],
            ['tv/tng/season5', true],
            ['tv/tng/season6', true],
            ['tv/tng/season7', true],
            ['tv/tng/season8', false],
            ['tv/ds9/season0', false],
            ['tv/ds9/season1', true],
            ['tv/ds9/season2', true],
            ['tv/ds9/season3', true],
            ['tv/ds9/season4', true],
            ['tv/ds9/season5', true],
            ['tv/ds9/season6', true],
            ['tv/ds9/season7', true],
            ['tv/ds9/season8', false],
            ['tv/voy/season0', false],
            ['tv/voy/season1', true],
            ['tv/voy/season2', true],
            ['tv/voy/season3', true],
            ['tv/voy/season4', true],
            ['tv/voy/season5', true],
            ['tv/voy/season6', true],
            ['tv/voy/season7', true],
            ['tv/voy/season8', false],
        ];
    }
}
