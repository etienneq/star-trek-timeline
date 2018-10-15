<?php
namespace EtienneQ\StarTrekTimeline\Sort\Comparator;

use EtienneQ\StarTrekTimeline\Data\Item;

interface ComparatorInterface
{
    public function compare(Item $item1, Item $item2):int;
}
