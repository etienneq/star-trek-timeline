<?php
namespace EtienneQ\StarTrekTimeline;

use League\Csv\Reader;
use EtienneQ\StarTrekTimeline\Data\ItemFactory;
use EtienneQ\StarTrekTimeline\Data\MetaDataFactory;
use EtienneQ\StarTrekTimeline\Sort\AutomatedSort;
use EtienneQ\StarTrekTimeline\Sort\ManualSort;
use EtienneQ\StarTrekTimeline\Sort\Comparator\StartStardate;
use EtienneQ\StarTrekTimeline\Sort\Comparator\StartDate;
use EtienneQ\StarTrekTimeline\Sort\Comparator\PublicationDate;
use EtienneQ\StarTrekTimeline\Sort\Comparator\Number;
use EtienneQ\StarTrekTimeline\Data\Item;
use EtienneQ\StarTrekTimeline\Data\ItemsFile;
use EtienneQ\StarTrekTimeline\Data\MetaDataFile;
use EtienneQ\StarTrekTimeline\Data\ItemException;
use EtienneQ\StarTrekTimeline\Filesystem\RecursiveDirectoryScanner;

class Timeline
{
    protected const RESOURCES_DIR = __DIR__.'/../resources';
    
    protected const DATA_FILE_HEADERS = [
        ItemsFile::NUMBER,
        ItemsFile::TITLE,
        ItemsFile::START_DATE,
        ItemsFile::END_DATE,
        ItemsFile::START_STARDATE,
        ItemsFile::END_STARDATE,
        ItemsFile::PUBLICATION_DATE,
        ItemsFile::PREDECESSOR_ID,
        ItemsFile::DESCRIPTION,
    ];
    
    /**
     * @var MetaDataFactory
     */
    protected $metaDataFactory;
    
    /**
     * @var ItemFactory
     */
    protected $itemFactory;
    
    /**
     * List of data files containing the timeline entries
     * @var array
     */
    protected $dataFiles = [];
    
    /**
     * @var AutomatedSort
     */
    protected $automatedSort;
    
    /**
     * @var ManualSort
     */
    protected $manualSort;

    /**
     * Timeline entries
     * @var Item[]
     */
    protected $entries = [];
    
    public function __construct()
    {
        $directoryScanner = new RecursiveDirectoryScanner();
        
        $metaDataFiles = $directoryScanner->getFiles(self::RESOURCES_DIR, MetaDataFile::FILE_ENDING);
        $this->metaDataFactory = new MetaDataFactory($metaDataFiles);
        
        $this->dataFiles = $directoryScanner->getFiles(self::RESOURCES_DIR, ItemsFile::FILE_ENDING);
        
        $this->itemFactory = new ItemFactory();
        
        $this->automatedSort = new AutomatedSort();
        $this->automatedSort->addComparator(new StartStardate());
        $this->automatedSort->addComparator(new StartDate());
        $this->automatedSort->addComparator(new PublicationDate());
        $this->automatedSort->addComparator(new Number());
        
        $this->manualSort = new ManualSort();
    }
    
    /**
     * Returns the complete timeline.
     * @return Item[]
     */
    public function findAll():array
    {
        if (empty($this->entries) === true) {
            $this->load();
        }
        
        return $this->entries;
    }
    
    /**
     * Loads and sorts timeline entries lazily.
     */
    protected function load():void
    {
        foreach ($this->dataFiles as $simpleFileName => $file) {
            $reader = Reader::createFromPath($file, 'r');
            $reader->setHeaderOffset(0);
            
            $lastParent = null;
            
            foreach($reader->getRecords(self::DATA_FILE_HEADERS) as $record) {
                $item = $this->itemFactory->createItem($record, $this->metaDataFactory->getMetaData($simpleFileName));
                
                if ($item->number  !== ItemsFile::NUMBER_CHILD) {
                    $lastParent = $item;
                } else {
                    if ($lastParent === null) {
                        throw new ItemException("Parent record not found for {$item->getId()}.");
                    }
                    
                    $item->setParent($lastParent);
                }
                
                if (empty($item->predecessorId) === true) {
                    $this->automatedSort->addItem($item);
                } else {
                    $this->manualSort->addItem($item);
                }
            }
        }
        
        $this->entries = $this->automatedSort->sort();
        $this->manualSort->injectInto($this->entries);
    }
}
