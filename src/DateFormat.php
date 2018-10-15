<?php
namespace EtienneQ\StarTrekTimeline;

class DateFormat
{
    /**
     * Regex pattern for a valid date expression
     * @var string
     */
    public const PATTERN_DATE = '/^(~)?(-)?([0-9]+)(-([0-9]{2}))?(-([0-9]{2}))?$/';
    
    public const PATTERN_FULL_DATE = '/^[0-9]+-[0-9]{2}-[0-9]{2}$/'; // YYYY-MM-DD
    public const PATTERN_YEAR_MONTH = '/^[0-9]+-[0-9]{2}$/'; // YYYY-MM
    
    public const DATE_POSITIONS = [
        'about' => 1,
        'bc' => 2,
        'year' => 3,
        'month' => 5,
        'day' => 7,
    ];
    
    public static function getYear(string $date):string
    {
        $matches = [];
        preg_match(self::PATTERN_DATE, $date, $matches);
        
        $date = '';
        
        if (empty($matches[self::DATE_POSITIONS['about']]) === false) {
            $date .= 'C.';
        }
        
        $year = $matches[self::DATE_POSITIONS['year']];
        if (empty($matches[self::DATE_POSITIONS['bc']]) === false && $year >= 10000) {
            $year = number_format($year);
        }
        
        $date .= $year;
        
        if (empty($matches[self::DATE_POSITIONS['bc']]) === false) {
            $date .= ' BC';
        }
        
        return $date;
    }
}
