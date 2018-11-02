<?php
namespace EtienneQ\StarTrekTimeline\Sort;

use EtienneQ\StarTrekTimeline\Data\Item;
use EtienneQ\StarTrekTimeline\Data\ItemException;

class ManualSort extends AbstractSort
{
    /**
     * Injects all items into target list.
     * @param Item[] $targetList
     */
    public function injectInto(array &$targetList):void
    {
        if (empty($this->items) === true) {
            return;
        }
        
        do {
            $itemsToInsert = array_filter($this->items, [$this, 'filterItemsWithoutReference']); // extract items that are not referenced themselves
            foreach ($itemsToInsert as $key => $item) {
                /** @var Item $item */
                $offset = array_search($item->predecessorId, array_keys($targetList));
                if ($offset === false) {
                    $exception = new ItemException($item->getId(), "Predecessor item {$item->predecessorId}.");
                    if ($this->strictMode === true) {
                        throw $exception;
                    } else {
                        $this->errors[] = $exception;
                        continue;
                    }
                }
                
                $offset++;
                $targetList = array_slice($targetList, 0, $offset, true) + [$key => $item] + array_slice($targetList, $offset, null, true);
            }
            
            $this->items = array_diff_key($this->items, $itemsToInsert); // update list of remaining items to be processed
        } while (count($this->items) > 0);
    }
    
    protected function filterItemsWithoutReference(Item $item):bool
    {
        return isset($this->items[$item->predecessorId]) === false;
    }
}
