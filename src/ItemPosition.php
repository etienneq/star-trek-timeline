<?php
namespace EtienneQ\StarTrekTimeline;

/**
 * Value object representing an items position in it's package plus additional attributes regarding position.
 */
class ItemPosition
{
    /**
     * @var int
     */
    protected $position;
    
    /**
     * @var float
     */
    protected $stardate;
    
    public function __construct(int $position, float $stardate)
    {
        $this->position = $position;
        $this->stardate = $stardate;
    }
    
    public function getPosition():int
    {
        return $this->position;
    }

    public function getStardate():float
    {
        return $this->stardate;
    }
}
