<?php
namespace EtienneQ\StarTrekTimeline\Sort\Comparator;

use EtienneQ\StarTrekTimeline\Data\Item;

/**
 * Compares by number if both items are from the same package.
 */
class Number implements ComparatorInterface
{
    public function compare(Item $item1, Item $item2):int
    {
        if ($item1->getPackage()->getId() !== $item2->getPackage()->getId()) {
            throw new NotApplicableException('Both items are not from the same package.');
        }
        
        return $item1->number <=> $item2->number;
    }
}
