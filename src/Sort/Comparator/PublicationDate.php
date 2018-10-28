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
        if (
            ($item1->getMetaData()->isTngEraTvSeries() === false || $item2->getMetaData()->isTngEraTvSeries() === false) &&
            $item1->getMetaData()->getId() !== $item2->getMetaData()->getId()
        ) {
            throw new NotApplicableException('At least on item is not from TNG era and both items are not from the same package.');
        }
        
        if (empty($item1->getPublicationDate()) === true) {
            throw new NotApplicableException('No publication date defined for item 1.');
        }
        
        if (empty($item2->getPublicationDate()) === true) {
            throw new NotApplicableException('No publication date defined for item 2.');
        }
        
        return (new \DateTime($item1->getPublicationDate()))->format('U') <=> (new \DateTime($item2->getPublicationDate()))->format('U');
    }
}
