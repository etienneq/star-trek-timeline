<?php
namespace EtienneQ\StarTrekTimeline\Sort\Comparator;

use EtienneQ\StarTrekTimeline\Data\Item;

/**
 * Compares by start stardate of both items's start stardates lie withing TNG stardate era.
 */
class StartStardate implements ComparatorInterface
{
    public function compare(Item $item1, Item $item2):int
    {
        if ($item1->isStartDateInTngStardateEra() === false) {
            throw new NotApplicableException('StartDate of item 1 does not lie within TNG stardate era.');
        }
        
        if ($item2->isStartDateInTngStardateEra() === false) {
            throw new NotApplicableException('StartDate of item 2 does not lie within TNG stardate era.');
        }
        
        if (empty($item1->getStartStardate()) === true) {
            throw new NotApplicableException('StartStardate of item 1 is not set.');
        }
        
        if (empty($item2->getStartStardate()) === true) {
            throw new NotApplicableException('StartStardate of item 2 is not set.');
        }
        
        return $item1->getStartStardate() <=> $item2->getStartStardate();
    }
}
