<?php
namespace EtienneQ\StarTrekTimeline\Sort;

use EtienneQ\StarTrekTimeline\Data\Item;

class ManualSort extends AbstractSort
{
    public function sort():array
    {
        
    }
    
    /**
     * Injects all items into target list.
     * @param Item[] $targetList
     */
    public function injectInto(array &$targetList):void
    {
        if (empty($this->items) === true) {
            throw new ItemException('No items were added to be injected into target list.');
        }
        
        do {
            $itemsToInsert = array_filter($this->items, [$this, 'filterItemsWithoutReference']); // extract items that are not referenced themselves
            foreach ($itemsToInsert as $key => $item) {
                $offset = array_search($item->after, array_keys($targetList));
                if ($offset === false) {
                    throw new ItemException("Predecessor item {$item->after} not found for {$key}.");
                }
                
                $offset++;
                $targetList = array_slice($targetList, 0, $offset, true) + [$key => $item] + array_slice($targetList, $offset, null, true);
            }
            
            $this->items = array_diff_key($this->items, $itemsToInsert); // update list of remaining items to be processed
        } while (count($this->items) > 0);
    }
    
    protected function filterItemsWithoutReference(Item $item):bool
    {
        return isset($this->items[$item->after]) === false;
    }
}
