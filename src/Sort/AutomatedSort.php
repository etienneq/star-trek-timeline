<?php
namespace EtienneQ\StarTrekTimeline\Sort;

use EtienneQ\StarTrekTimeline\Data\Item;
use EtienneQ\StarTrekTimeline\Sort\Comparator\ComparatorInterface;
use EtienneQ\StarTrekTimeline\Sort\Comparator\NotApplicableException;
use EtienneQ\StarTrekTimeline\Sort\Comparator\StartDateMorePreciseException;

class AutomatedSort extends AbstractSort
{
    /**
     * @var ComparatorInterface[]
     */
    protected $comparators =[];
    
    public function addComparator(ComparatorInterface $comparator):void
    {
        $this->comparators[] = $comparator;
    }
    
    public function sort():array
    {
        if (empty($this->items) === true || count($this->items) <= 1) {
            throw new ItemException('At least two items must be added to sort.');
        }
        
        if (empty($this->comparators) === true) {
            throw new NoComparatorException('Add at least one comparator.');
        }
        
        uasort($this->items, [$this, 'comparatorStack']);
        
        return $this->items;
    }
    
    protected function comparatorStack(Item $item1, Item $item2):int
    {
        $result = false;
        $itemHavingMorePreciseStartDate = null;
        foreach ($this->comparators as $comparator) {
            try {
                $result = $comparator->compare($item1, $item2);
            } catch (StartDateMorePreciseException $e) {
                $itemHavingMorePreciseStartDate = $e->getItem();
                continue;
            } catch (NotApplicableException $e) {
                continue;
            } 
            
            if ($result !== 0) { // compared but not equal
                return $result;
            }
        }
        
        if ($result === false) {
            if ($itemHavingMorePreciseStartDate !== null) {
                if ($item1->getId() === $itemHavingMorePreciseStartDate->getId()) {
                    return 1;
                } else {
                    return -1;
                }
            }
            
            throw new NotApplicableException("No applicable comparator found to compare {$item1->getId()} and {$item2->getId()}.");
        }
        
        return $result;
    }
}
