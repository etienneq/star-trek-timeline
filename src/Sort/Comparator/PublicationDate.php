<?php
namespace EtienneQ\StarTrekTimeline\Sort\Comparator;

use EtienneQ\StarTrekTimeline\Data\Item;

/**
 * Compares by pulication date if both items are from a TNG-era series or from the same package and publication is date defined.
 */
class PublicationDate implements ComparatorInterface
{
    public function compare(Item $item1, Item $item2):int
    {
        if ($this->areFromSameEra($item1, $item2) === false && $this->areFromSamePackage($item1, $item2) === false) {
            throw new NotApplicableException('Items must be from same era or same package.');
        }
        
        if (empty($item1->getPublicationDate()) === true) {
            throw new NotApplicableException('No publication date defined for item 1.');
        }
        
        if (empty($item2->getPublicationDate()) === true) {
            throw new NotApplicableException('No publication date defined for item 2.');
        }
        
        return (new \DateTime($item1->getPublicationDate()))->format('U') <=> (new \DateTime($item2->getPublicationDate()))->format('U');
    }
    
    protected function areFromSameEra(Item $item1, Item $item2):bool
    {
        if ($item1->getMetaData()->isEntEraTvSeries() === true && $item2->getMetaData()->isEntEraTvSeries() === true) {
            return true;
        }
        
        
        if ($item1->getMetaData()->isTosEraTvSeries() === true && $item2->getMetaData()->isTosEraTvSeries() === true) {
            return true;
        }
        
        if ($item1->getMetaData()->isTngEraTvSeries() === true && $item2->getMetaData()->isTngEraTvSeries() === true) {
            return true;
        }
        
        return false;
    }
    
    protected function areFromSamePackage(Item $item1, Item $item2):bool
    {
        return $item1->getMetaData()->getId() === $item2->getMetaData()->getId();
    }
}
