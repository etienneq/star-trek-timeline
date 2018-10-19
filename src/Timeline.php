<?php
namespace EtienneQ\StarTrekTimeline;

use League\Csv\Reader;
use EtienneQ\StarTrekTimeline\Data\ItemFactory;
use EtienneQ\StarTrekTimeline\Data\PackageFactory;
use EtienneQ\StarTrekTimeline\Sort\AutomatedSort;
use EtienneQ\StarTrekTimeline\Sort\ManualSort;
use EtienneQ\StarTrekTimeline\Sort\Comparator\StartStardate;
use EtienneQ\StarTrekTimeline\Sort\Comparator\StartDate;
use EtienneQ\StarTrekTimeline\Sort\Comparator\PublicationDate;
use EtienneQ\StarTrekTimeline\Sort\Comparator\Number;
use EtienneQ\StarTrekTimeline\Data\Item;
use EtienneQ\StarTrekTimeline\Data\DataFile;
use EtienneQ\StarTrekTimeline\Data\MetaDataFile;

class Timeline
{
    protected const RESOURCES_DIR = __DIR__.'/../resources';
    
    protected const DATA_FILE_HEADERS = [
        DataFile::NUMBER,
        DataFile::TITLE,
        DataFile::START_DATE,
        DataFile::END_DATE,
        DataFile::START_STARDATE,
        DataFile::END_STARDATE,
        DataFile::PUBLICATION_DATE,
        DataFile::PREDECESSOR_ID,
        DataFile::DESCRIPTION,
    ];
    
    /**
     * @var PackageFactory
     */
    protected $packageFactory;
    
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
        $this->packageFactory = new PackageFactory($metaDataFiles);
        
        $this->dataFiles = $directoryScanner->getFiles(self::RESOURCES_DIR, DataFile::FILE_ENDING);
        
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
                $item = $this->itemFactory->createItem($record, $this->packageFactory->getPackage($simpleFileName));
                
                if ($item->number  !== DataFile::NUMBER_CHILD) {
                    $lastParent = $item;
                } else {
                    if ($lastParent === null) {
                        throw new \Exception("Parent record not found for {$item->getId()}.");
                    }
                    
                    $item->setParent($lastParent);
                }
                
                if (empty($item->after) === true) {
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
