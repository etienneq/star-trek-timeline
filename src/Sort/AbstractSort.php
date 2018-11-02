<?php
namespace EtienneQ\StarTrekTimeline\Sort;

use EtienneQ\StarTrekTimeline\Data\Item;

abstract class AbstractSort
{
    /**
     * @var Item[]
     */
    protected $items =[];
    
    /**
     * @var bool
     */
    protected $strictMode = false;
    
    /**
     * @var array
     */
    protected $errors = [];
    
    /**
     *
     * @param bool $strictMode abort loading if true, skip erroneous items if false
     */
    public function __construct(bool $strictMode = true)
    {
        $this->strictMode = $strictMode;
    }
    
    public function getErrors():array
    {
        return $this->errors;
    }
    
    public function addItem(Item $item)
    {
        $this->items[$item->getId()] = $item;
    }
}
