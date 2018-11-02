<?php
namespace EtienneQ\StarTrekTimeline;

class DateFormat
{
    /**
     * Regex pattern for a valid date expression
     * @var string
     */
    public const PATTERN_DATE = '/^(~)?(-)?([0-9]+)(-([0-9]{2}))?(-([0-9]{2}))?$/';
    
    public const DATE_POSITIONS = [
        self::POS_CIRCA => 1,
        self::POS_BEFORE_CHRIST => 2,
        self::POS_YEAR => 3,
        self::POS_MONTH => 5,
        self::POS_DAY => 7,
    ];
    
    public const POS_CIRCA = 0;
    public const POS_BEFORE_CHRIST = 1;
    public const POS_YEAR = 2;
    public const POS_MONTH = 3;
    public const POS_DAY = 4;
    
    public const FORMAT_FULL_DATE = 'Y-m-d';
    
    protected const PATTERN_FULL_DATE = '/^[0-9]+-[0-9]{2}-[0-9]{2}$/'; // YYYY-MM-DD
    protected const PATTERN_YEAR_MONTH = '/^[0-9]+-[0-9]{2}$/'; // YYYY-MM
    
    public static function isValidDateExpression(string $date):bool
    {
        return preg_match(self::PATTERN_DATE, $date) === 1;
    }
    
    public static function isValidFullDate(string $date):bool
    {
        return preg_match(self::PATTERN_FULL_DATE, $date) === 1;
    }
    
    public static function isValidYearMonth(string $date):bool
    {
        return preg_match(self::PATTERN_YEAR_MONTH, $date) === 1;
    }
    
    public static function getYear(string $date):string
    {
        $dateParts = [];
        preg_match(self::PATTERN_DATE, $date, $dateParts);
        return $dateParts[self::DATE_POSITIONS[self::POS_BEFORE_CHRIST]].$dateParts[self::DATE_POSITIONS[self::POS_YEAR]];
        
    }
    
    /**
     * Renders year for display.
     */
    public static function renderYear(string $date):string
    {
        $matches = [];
        preg_match(self::PATTERN_DATE, $date, $matches);
        
        $date = '';
        
        if (empty($matches[self::DATE_POSITIONS[self::POS_CIRCA]]) === false) {
            $date .= 'C.';
        }
        
        $year = $matches[self::DATE_POSITIONS[self::POS_YEAR]];
        if (empty($matches[self::DATE_POSITIONS[self::POS_BEFORE_CHRIST]]) === false && $year >= 10000) {
            $year = number_format($year);
        }
        
        $date .= $year;
        
        if (empty($matches[self::DATE_POSITIONS[self::POS_BEFORE_CHRIST]]) === false) {
            $date .= ' BC';
        }
        
        return $date;
    }
}
