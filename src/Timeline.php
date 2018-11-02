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
use EtienneQ\StarTrekTimeline\Filesystem\FileException;
use EtienneQ\Stardate\Calculator;
use EtienneQ\Stardate\InvalidDateException;

class Timeline
{
    protected const RESOURCES_DIR = __DIR__.'/../resources';
    
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
            
            if ($reader->getHeader() !== ItemsFile::DATA_FILE_HEADERS) {
                $unnecessaryFields = array_diff($reader->getHeader(), ItemsFile::DATA_FILE_HEADERS);
                $missingFields = array_diff(ItemsFile::DATA_FILE_HEADERS, $reader->getHeader());
                $message = "{$file} does not contain the expected header.";
                if (count($unnecessaryFields) > 0 || count($missingFields) > 0) {
                    if (count($unnecessaryFields) > 0) {
                        $message .= ' The following fields were found but are not necessary: '.implode(', ', $unnecessaryFields).'.';
                    } elseif (count($missingFields) > 0) {
                        $message .= ' The following fields are missing: '.implode(', ', $missingFields).'.';
                    }
                } else {
                    $message .= ' The fields are not in the expected order.'.
                        ' Expected: '.implode(', ', ItemsFile::DATA_FILE_HEADERS)
                        . '. Actual: '.implode(', ', $reader->getHeader());
                }
                
                throw new FileException($message);
            }
            
            $metaData = $this->metaDataFactory->getMetaData($simpleFileName);
            
            $lastParent = null;
            
            // set default: minimum stardate in current season
            if ($metaData->isTngEraTvSeries() === true) {
                $firstRecord = $reader->fetchOne();
                $stardate = (new Calculator())->toStardate(new \DateTime(DateFormat::getYear($firstRecord['startDate']).'-01-01'));
                $previousItemPosition = new ItemPosition(0, $stardate);
            }
            
            $nextItemPosition = null;
            
            $records = $reader->getRecords();
            foreach($records as $record) {
                $item = $this->itemFactory->createItem($record, $metaData);
                
                if ($item->number !== ItemsFile::NUMBER_CHILD) {
                    $lastParent = $item;
                } else {
                    if ($lastParent === null) {
                        throw new ItemException("Parent record not found for {$item->getId()}.");
                    }
                    
                    $item->setParent($lastParent);
                }
                
                // Save last known stardate
                if ($item->hasParent() === false && $metaData->isTngEraTvSeries() === true && $item->getStartStardate() !== null) {
                    $previousItemPosition = new ItemPosition($records->key(), $item->getStartStardate());
                }
                
                // Extrapolate stardate
                // Note: If an future item's stardate is wrong or stardates jump back and forth the calculation will not be correct
                if ($item->hasParent() === false && $metaData->isTngEraTvSeries() === true && $item->getStartStardate() === null) {
                    try {
                        $backupnextItemPosition = $nextItemPosition;
                        $nextItemPosition = $this->getNextStartStardate($records, $reader, $item);
                        $stardate = $this->extrapolateStardate($previousItemPosition, $nextItemPosition, $records->key());
                        $item->setStartStardate($stardate);
                    } catch (InvalidDateException $exception) {
                        $nextItemPosition = $backupnextItemPosition;
                    }
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
    
    protected function getNextStartStardate(\Iterator $iterator, Reader $reader, Item $item):ItemPosition
    {
        $currentIndex = $iterator->key();
        
        $index = $reader->count();
        
        // default: maximum stardate in current season
        $stardate = (new Calculator())->toStardate(new \DateTime(DateFormat::getYear($item->getStartDate()).'-01-01')) + 999;

        // search for nearest stardate in the future
        for ($i = $currentIndex; $i < $reader->count(); $i++) {
            $record = $reader->fetchOne($i);
            if (empty($record[ItemsFile::START_STARDATE]) === false) {
                $stardate = $record[ItemsFile::START_STARDATE];
                $index = $i + 1;
                break;
            }
        }
        
        // reset iterator
        $iterator->rewind();
        for ($i = 1; $i < $currentIndex; $i++) {
            $iterator->next();
        }
        
        $itemPosition = new ItemPosition($index, (float)$stardate);
        return $itemPosition;
    }
    
    protected function extrapolateStardate(ItemPosition $previous, ItemPosition $next, int $currentPos, int $precicion = 2):float
    {
        $lowerPos = $previous->getPosition();
        $lowerStardate = $previous->getStardate();
        $upperPos = $next->getPosition();
        $upperStardate = $next->getStardate();
        
        $stardate = $lowerStardate + ($upperStardate - $lowerStardate) / ($upperPos - $lowerPos) * ($currentPos - $lowerPos);
        return round($stardate, $precicion);
    }
}
