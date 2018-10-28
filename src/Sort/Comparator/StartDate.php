<?php
namespace EtienneQ\StarTrekTimeline\Sort\Comparator;

use EtienneQ\StarTrekTimeline\Data\Item;
use EtienneQ\StarTrekTimeline\DateFormat;

/**
 * Compares by start date as exactly as possible.
 */
class StartDate implements ComparatorInterface
{
    public function compare(Item $item1, Item $item2):int
    {
        $dateParts1 = [];
        $dateParts2 = [];
        
        preg_match(DateFormat::PATTERN_DATE, $item1->getStartDate(), $dateParts1);
        preg_match(DateFormat::PATTERN_DATE, $item2->getStartDate(), $dateParts2);
        
        $bothBeforeChrist = false;
        foreach (DateFormat::DATE_POSITIONS as $type => $position) {
            if ($type === DateFormat::POS_BEFORE_CHRIST) {
                if (empty($dateParts1[$position]) === false && empty($dateParts2[$position]) === true) { // first one is B.C.
                    return -1;
                } elseif (empty($dateParts1[$position]) === true && empty($dateParts2[$position]) === false) { // second one is B.C.
                    return 1;
                } elseif (empty($dateParts1[$position]) === false && empty($dateParts2[$position]) === false) { // both are B.C.
                    $bothBeforeChrist = true;
                } else {
                    $bothBeforeChrist = false;
                }
            } elseif ($type === DateFormat::POS_YEAR) {
                if (
                    ($dateParts1[$position] === '0' || empty($dateParts1[$position]) === false) &&
                    ($dateParts2[$position] === '0' || empty($dateParts2[$position]) === false)
                    ) { // both set
                        $result = $dateParts1[$position] <=> $dateParts2[$position];
                        if ($bothBeforeChrist === true) {
                            $result = $result * -1;
                        }
                        
                        if ($result !== 0) { // not equal
                            return $result;
                        }
                }
            } elseif (in_array($type, [DateFormat::POS_MONTH, DateFormat::POS_DAY]) === true) {
                if (empty($dateParts1[$position]) === false && empty($dateParts2[$position]) === false) { // both set
                    $result = $dateParts1[$position] <=> $dateParts2[$position];
                    
                    if ($result !== 0) { // not equal
                        return $result;
                    }
                } elseif(empty($dateParts1[$position]) === false) {
                    throw new StartDateMorePreciseException('Date expression of item 1 is more precise than that of item 2', $item1);
                } else {
                    throw new StartDateMorePreciseException('Date expression of item 2 is more precise than that of item 1', $item2);
                }
            } else {
                continue;
            }
        }
        
        return 0;
    }
}
