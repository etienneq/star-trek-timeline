<?php
namespace Tests\EtienneQ\StarTrekTimeline\Data;

use EtienneQ\StarTrekTimeline\Data\Package;
use PHPUnit\Framework\TestCase;

/**
 * @covers \EtienneQ\StarTrekTimeline\Data\Package
 */
class PackageTest extends TestCase
{
    public function testGetIdShouldReturnId()
    {
        $expectedId = 'id';
        $package = new Package($expectedId);
        
        $this->assertEquals($expectedId, $package->getId());
    }
    
    /**
     * @dataProvider dataProviderPackageId
     */
    public function testIsInTngEraShouldReturnBool(string $id, bool $result)
    {
        $package = new Package($id);
        
        $this->assertEquals($result, $package->isInTngEra());
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
