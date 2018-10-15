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
        $datePartsA = [];
        $datePartsB = [];
        
        preg_match(DateFormat::PATTERN_DATE, $item1->getStartDate(), $datePartsA);
        preg_match(DateFormat::PATTERN_DATE, $item2->getStartDate(), $datePartsB);
        
        $bothBeforeChrist = false;
        foreach (DateFormat::DATE_POSITIONS as $type => $position) {
            if ($type === 'bc') {
                if (empty($datePartsA[$position]) === false && empty($datePartsB[$position]) === true) { // first one is B.C.
                    return -1;
                } elseif (empty($datePartsA[$position]) === true && empty($datePartsB[$position]) === false) { // second one is B.C.
                    return 1;
                } elseif (empty($datePartsA[$position]) === false && empty($datePartsB[$position]) === false) { // both are B.C.
                    $bothBeforeChrist = true;
                } else {
                    $bothBeforeChrist = false;
                }
            } elseif ($type === 'year') {
                if (
                    ($datePartsA[$position] === '0' || empty($datePartsA[$position]) === false) &&
                    ($datePartsB[$position] === '0' || empty($datePartsB[$position]) === false)
                    ) { // both set
                        $result = $datePartsA[$position] <=> $datePartsB[$position];
                        if ($bothBeforeChrist === true) {
                            $result = $result * -1;
                        }
                        
                        if ($result !== 0) { // not equal
                            return $result;
                        }
                }
            }elseif (in_array($type, ['month', 'day']) === true) {
                if (empty($datePartsA[$position]) === false && empty($datePartsB[$position]) === false) { // both set
                    $result = $datePartsA[$position] <=> $datePartsB[$position];
                    
                    if ($result !== 0) { // not equal
                        return $result;
                    }
                }
            } else {
                continue;
            }
        }
        
        return 0;
    }
}
