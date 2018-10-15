<?php
namespace EtienneQ\StarTrekTimeline\Sort;

use EtienneQ\StarTrekTimeline\Data\Item;

abstract class AbstractSort
{
    /**
     * @var Item[]
     */
    protected $items =[];
    
    public function addItem(Item $item)
    {
        $this->items[$item->getId()] = $item;
    }
}
