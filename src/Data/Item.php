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
    public $publicationDate = '';
    
    /**
     * @var string
     */
    public $after = '';
    
    /**
     * @var string
     */
    public $details = '';
   
    /**
     * @var string
     */
    protected $id = '';
    
    /**
     * @var Package
     */
    protected $package;
    
    /**
     * @var Item
     */
    protected $parent;
    
    /**
     * @var string
     */
    protected $title = '';
    
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
     * @var Calculator
     */
    protected $calculator;
    
    public function __construct(string $id, string $title, string $startDate, Calculator $calculator)
    {
        if (empty($title) === true) {
            throw new InvalidItemAttributeException('Title must not be empty.');
        }
        
        if (preg_match(DateFormat::PATTERN_DATE, $startDate) !== 1) {
            throw new InvalidItemAttributeException('StartDate is empty or invalid.');
        }
        
        $this->id = $id;
        $this->title = $title;
        $this->startDate = $startDate;
        
        $this->calculator = $calculator;
    }

    public function getId():string
    {
        return $this->id;
    }
    
    public function setPackage(Package $package):void
    {
        $this->package = $package;
    }
    
    public function getPackage():Package
    {
        return $this->package;
    }
    
    public function setParent(Item $parent):void
    {
        $this->parent = $parent;
        $this->publicationDate = $parent->publicationDate;
    }
    
    public function getParent():Item
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
        if (empty($date) === false && preg_match(DateFormat::PATTERN_DATE, $date) !== 1) {
            throw new InvalidItemAttributeException('EndDate is invalid.');
        }
        
        $this->endDate = $date;
    }
    
    public function getEndDate():string
    {
        return $this->endDate;
    }
    
    public function setStartStardate(float $stardate):void
    {
        if ($stardate < 0) {
            throw new InvalidItemAttributeException('Stardate must be greater than 0.');
        }
        
        $this->startStardate = $stardate;
        $this->startDate = $this->calculateDateFromStardate($this->startDate, $stardate);
    }
    
    public function getStartStardate():?float
    {
        return $this->startStardate;
    }
    
    public function setEndStardate(float $stardate):void
    {
        if ($stardate < 0) {
            throw new InvalidItemAttributeException('Stardate must be greater than 0.');
        }
        
        $this->endStardate = $stardate;
        $this->endDate = $this->calculateDateFromStardate($this->endDate, $stardate);
    }
    
    public function getEndStardate():?float
    {
        return $this->endStardate;
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
            return $this->calculator->toGregorianDate($stardate)->format('Y-m-d');
        }
        
        return $date;
    }
    
    protected function isInTngStardateEra(string $date):bool
    {
        $dateParts = [];
        preg_match(DateFormat::PATTERN_DATE, $date, $dateParts);
        $year = $dateParts[DateFormat::DATE_POSITIONS['bc']].$dateParts[DateFormat::DATE_POSITIONS['year']];
        return $year >= $this->calculator::MIN_YEAR;
    }
}