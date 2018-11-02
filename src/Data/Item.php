<?php
namespace EtienneQ\StarTrekTimeline\Data;

use EtienneQ\Stardate\Calculator;
use EtienneQ\StarTrekTimeline\DateFormat;

/**
 * Value object representing an item (story, episode or movie).
 */
class Item
{
    /**
     * @var string
     */
    public $number = '';

    /**
     * @var string
     */
    public $title = '';
    
    /**
     * @var string
     */
    public $predecessorId = '';
    
    /**
     * @var string
     */
    public $description = '';
    
    /**
     * @var string
     */
    public $historiansNote = '';
    
    /**
     * @var string
     */
    public $sections = '';
   
    /**
     * @var string
     */
    protected $id = '';
    
    /**
     * @var MetaData
     */
    protected $metaData;
    
    /**
     * @var Item
     */
    protected $parent;
    
    /**
     * @var string
     */
    protected $startDate = '';
    
    /**
     * @var string
     */
    protected $endDate = '';
    
    /**
     * @var float
     */
    protected $startStardate;
    
    /**
     * @var float
     */
    protected $endStardate;
    
    /**
     * @var string
     */
    protected $publicationDate = '';
    
    /**
     * @var Calculator
     */
    protected $calculator;
    
    public function __construct(string $id, string $startDate, Calculator $calculator)
    {
        if (preg_match(DateFormat::PATTERN_DATE, $startDate) !== 1) {
            throw new ItemException('Start date is empty or invalid.');
        }
        
        $this->id = $id;
        $this->startDate = $startDate;
        $this->calculator = $calculator;
        
        $stardate = $this->calculateStardateFromDate($startDate);
        if ($stardate !== null) {
            $this->startStardate = $stardate;
        }
    }

    public function getId():string
    {
        return $this->id;
    }
    
    public function setMetaData(MetaData $metaData):void
    {
        $this->metaData = $metaData;
    }
    
    public function getMetaData():MetaData
    {
        return $this->metaData;
    }
    
    public function setParent(Item $parent):void
    {
        $this->parent = $parent;
        $this->number = $parent->number;
        $this->title = $parent->title;
        $this->publicationDate = $parent->getPublicationDate();
        $this->description = $parent->description;
    }
    
    public function getParent():?Item
    {
        return $this->parent;
    }
    
    public function getTitle():string
    {
        return $this->title;
    }
    
    public function getStartDate():string
    {
        return $this->startDate;
    }
    
    public function setEndDate(string $date):void
    {
        if (preg_match(DateFormat::PATTERN_DATE, $date) !== 1) {
            throw new ItemException('End date is invalid.');
        }
        
        $this->endDate = $date;
        
        $stardate = $this->calculateStardateFromDate($date);
        if ($stardate !== null) {
            $this->endStardate = $stardate;
        }
    }
    
    public function getEndDate():string
    {
        return $this->endDate;
    }
    
    public function setStartStardate(float $stardate):void
    {
        if ($stardate < 0) {
            throw new ItemException('Start stardate must be greater than 0.');
        }
        
        $this->startStardate = $stardate;
        
        $date = $this->calculateDateFromStardate($this->startDate, $stardate);
        if (strpos($date, $this->startDate) === false) {
            throw new ItemException('Calculated start date contradicts preset start date.');
        } else {        
            $this->startDate = $date;
        }
    }
    
    public function getStartStardate():?float
    {
        return $this->startStardate;
    }
    
    public function setEndStardate(float $stardate):void
    {
        if ($stardate < 0) {
            throw new ItemException('End stardate must be greater than 0.');
        }
        
        $this->endStardate = $stardate;
        
        if (empty($this->endDate) === false) {
            $date = $this->calculateDateFromStardate($this->endDate, $stardate);
            if (strpos($date, $this->endDate) === false) {
                throw new ItemException('Calculated end date contradicts preset end date.');
            } else {
                $this->endDate = $date;
            }
        }
    }
    
    public function getEndStardate():?float
    {
        return $this->endStardate;
    }
    
    public function setPublicationDate(string $date):void
    {
        if (preg_match(DateFormat::PATTERN_FULL_DATE, $date) !== 1) {
            throw new ItemException('Publication date is invalid.');
        }
        
        $this->publicationDate = $date;
    }
    
    public function getPublicationDate():?string
    {
        return $this->publicationDate;
    }
    
    public function isStartDateInTngStardateEra():bool
    {
        return $this->isInTngStardateEra($this->startDate);
    }
    
    protected function calculateDateFromStardate(string $date, string $stardate):string
    {
        if (empty($date) === true || empty($stardate) === true) {
            return $date;
        }
        
        if ($this->isInTngStardateEra($date) === true && $stardate < $this->calculator::MAX_STARDATE) {
            return $this->calculator->toGregorianDate($stardate)->format(DateFormat::FORMAT_FULL_DATE);
        }
        
        return $date;
    }
    
    protected function calculateStardateFromDate(string $date):?float
    {
        if ($this->isInTngStardateEra($date) === true && preg_match(DateFormat::PATTERN_FULL_DATE, $date) === 1) {
            return $this->calculator->toStardate(new \DateTime($date.' 12:00:00'));
        }
        
        return null;
    }
    
    protected function isInTngStardateEra(string $date):bool
    {
        $dateParts = [];
        preg_match(DateFormat::PATTERN_DATE, $date, $dateParts);
        $year = $dateParts[DateFormat::DATE_POSITIONS[DateFormat::POS_BEFORE_CHRIST]].$dateParts[DateFormat::DATE_POSITIONS[DateFormat::POS_YEAR]];
        return $year >= $this->calculator::MIN_YEAR;
    }
}
