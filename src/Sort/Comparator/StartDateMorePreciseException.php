<?php
namespace EtienneQ\StarTrekTimeline\Sort\Comparator;

use EtienneQ\StarTrekTimeline\Data\Item;

class StartDateMorePreciseException extends \RuntimeException
{
    /**
     * @var Item
     */
    protected $item;
    
    public function __construct (string $message, Item $item) {
        parent::__construct($message);
        $this->item = $item;
    }
    
    public function getItem():Item
    {
        return $this->item;
    }
}
