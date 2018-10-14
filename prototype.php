<?php

// Prototype
// Will be rewritten to proper OO code.

use League\Csv\Reader;
use EtienneQ\Stardate\Calculator;
use EtienneQ\StarTrekTimeline\PackageFactory;
use EtienneQ\StarTrekTimeline\RecursiveDirectoryScanner;

require_once __DIR__.'/vendor/autoload.php';

$startTime = microtime(true);

$resourcesDir = __DIR__.'/resources';

$calculator = new Calculator();

$fullDatePattern = '/^[0-9]+-[0-9]{2}-[0-9]{2}$/'; // YYYY-MM-DD
$monthDatePattern = '/^[0-9]+-[0-9]{2}$/'; // YYYY-MM

$datePattern = '/^(~)?(-)?([0-9]+)(-([0-9]{2}))?(-([0-9]{2}))?$/'; // valid date expression
$datePositions = [
    'about' => 1,
    'bc' => 2,
    'year' => 3,
    'month' => 5,
    'day' => 7,
];

$tngEraSeries = [
    'tv/tng/season1',
    'tv/tng/season2',
    'tv/tng/season3',
    'tv/tng/season4',
    'tv/tng/season5',
    'tv/tng/season6',
    'tv/tng/season7',
    'tv/ds9/season1',
    'tv/ds9/season2',
    'tv/ds9/season3',
    'tv/ds9/season4',
    'tv/ds9/season5',
    'tv/ds9/season6',
    'tv/ds9/season7',
    'tv/voy/season1',
    'tv/voy/season2',
    'tv/voy/season3',
    'tv/voy/season4',
    'tv/voy/season5',
    'tv/voy/season6',
    'tv/voy/season7',
];

$scanner = new RecursiveDirectoryScanner();

// Load meta data for all packages
$metaDataFiles = $scanner->getFiles($resourcesDir, 'json');
$packageFactory = new PackageFactory($metaDataFiles);

// Load all data files
$dataFiles = $scanner->getFiles($resourcesDir, 'csv');

$fileHeaders = ['number', 'title', 'startDate', 'endDate', 'startStardate', 'endStardate', 'publicationDate', 'after', 'details'];

$items = [];
$itemsManualSort = [];

foreach ($dataFiles as $simpleFileName => $file) {
    $reader = Reader::createFromPath($file, 'r');
    
    $reader->setHeaderOffset(0);
    $headers = $reader->getHeader(); 
    if ($headers != $fileHeaders) {
        throw new \Exception("Headers in {$simpleFileName} do not match expectations.");
    }
    
    $headers = array_merge(['package', 'key'], $headers);

    $lastParentRecord = null;

    foreach($reader->getRecords() as $record) {
        $pathInfo = pathinfo($simpleFileName);
        $packageName = $pathInfo['dirname'].'/'.$pathInfo['filename'];
        $record['package'] = $packageFactory->getPackage($packageName);
        
        if (empty($record['number']) === true || $record['number'] === '--') {
            $words = preg_split("/\s+/", trim(preg_replace('/[^a-z0-9]/i', ' ', $record['title'])));
            $acronym = '';
            foreach ($words as $word) {
                $acronym .= $word[0];
            }
            $acronym = strtoupper($acronym);
        } else {
            $acronym = $record['number'];
        }
        
        $record['key'] = $record['package']->getId().'-'.$acronym;
        
        if (empty($record['title']) === true) {
            throw new \Exception("Title must not be empty. Missing for {$record['key']}");
        }
        
        $datePartsStartDate = [];
        $datePartsEndDate = [];
        
        if (preg_match($datePattern, $record['startDate'], $datePartsStartDate) !== 1) {
            throw new \Exception("Start date is empty or invalid for {$record['key']}");
        }
        
        if (empty($record['endDate']) === false && preg_match($datePattern, $record['endDate'], $datePartsEndDate) !== 1) {
            throw new \Exception("Invalid endDate given for {$record['key']}: {$record['endDate']}");
        }
        
        // Overwrites startDate and endDate when stardate is given
        $isTngEraTvSeries = in_array($record['package']->getId(), $tngEraSeries);
        $isStartYearDefinedAndInTngStardateEra = empty($datePartsStartDate[$datePositions['year']]) === false &&
            $datePartsStartDate[$datePositions['year']] >= Calculator::MIN_YEAR;
        if ($isTngEraTvSeries === true || $isStartYearDefinedAndInTngStardateEra === true) {
            if (empty($record['startStardate']) === false && $record['startStardate'] < Calculator::MAX_STARDATE) {
                $record['startDate'] = $calculator->toGregorianDate($record['startStardate'])->format('Y-m-d');
            }
            if (empty($record['endStardate']) === false && $record['endStardate'] < Calculator::MAX_STARDATE) {
                $record['endDate'] = $calculator->toGregorianDate($record['endStardate'])->format('Y-m-d');
            }
        }
        
        if ($record['number'] !== '--') {
            $lastParentRecord = $record;
        }
        
        if ($record['number'] === '--') {
            if ($lastParentRecord === null) {
                throw new \Exception("Parent record not found for {$record['key']}.");
            }
            
            $record['publicationDate'] = $lastParentRecord['publicationDate'];
            $record['parent'] = $lastParentRecord;
        } 
        
        
        if (empty($record['after']) === true) {
            $items[$record['key']] = $record;
        } else {
            $itemsManualSort[$record['key']] = $record;
        }
    }
}

// Sort all entries that can be automatically sorted.
$sort = function($a, $b) use ($tngEraSeries, $datePattern, $datePositions) {
    $result = false;
    
        $datePartsA = [];
        $datePartsB = [];
        
        preg_match($datePattern, $a['startDate'], $datePartsA);
        preg_match($datePattern, $b['startDate'], $datePartsB);
        
        // Sort by start stardate (TNG-era only)
        $isATngEraTvSeries = in_array($a['package']->getId(), $tngEraSeries);
        $isBTngEraTvSeries = in_array($b['package']->getId(), $tngEraSeries);
        
        $isAStartYearDefinedAndInTngStardateEra = empty($datePartsA[$datePositions['year']]) === false &&
        $datePartsA[$datePositions['year']] >= Calculator::MIN_YEAR;
            $isBStartYearDefinedAndInTngStardateEra = empty($datePartsB[$datePositions['year']]) === false &&
            $datePartsB[$datePositions['year']] >= Calculator::MIN_YEAR;
           
        if ($isATngEraTvSeries === true && $isBTngEraTvSeries === true &&
            $isAStartYearDefinedAndInTngStardateEra === true && $isBStartYearDefinedAndInTngStardateEra === true &&
            empty($a['startStardate']) === false && empty($b['startStardate']) === false
        ) {
            $result = (float)$a['startStardate'] <=> (float)$b['startStardate'];
        }
       
        if ($result !== false && $result !== 0) { // compared but not equal
            return $result;
        }
        
        // Sort by start date as exactly as possible
        $bothBeforeChrist = false;
        foreach ($datePositions as $type => $position) {
            if ($type === 'bc') {
                if (empty($datePartsA[$position]) === false && empty($datePartsB[$position]) === true) { // first one is B.C.
                    $result = -1;
                    break;
                } elseif (empty($datePartsA[$position]) === true && empty($datePartsB[$position]) === false) { // second one is B.C.
                    $result = 1;
                    break;
                } elseif (empty($datePartsA[$position]) === false && empty($datePartsB[$position]) === false) { // both are B.C.
                    $bothBeforeChrist = true;
                }
            } elseif (in_array($type, ['year', 'month', 'day']) === true) {
                if (empty($datePartsA[$position]) === false && empty($datePartsB[$position]) === false) { // both set
                    $result = $datePartsA[$position] <=> $datePartsB[$position];
                    if ($type === 'year' && $bothBeforeChrist === true) {
                        $result = $result * -1;
                    }
                    
                    if ($result !== 0) { // not equal
                        break;
                    }
                }
            }
        }
    
    if ($result !== false && $result !== 0) { // compared but not equal
        return $result;
    }
    
    // TNG-era series or same package AND pub date defined -> sort by pub date
    if ((
           (in_array($a['package']->getId(), $tngEraSeries) === true && in_array($b['package']->getId(), $tngEraSeries) === true) ||
           $a['package']->getId() === $b['package']->getId()
        ) &&
        empty($a['publicationDate']) === false &&
        empty($b['publicationDate']) === false
    ) {
        $result = (new \DateTime($a['publicationDate']))->format('U') <=> (new \DateTime($b['publicationDate']))->format('U');
    }
    
    if ($result !== false && $result !== 0) { // compared but not equal
        return $result;
    }
    
    // Same package -> sort by number
    if ($a['package']->getId() === $b['package']->getId()) {
        $result = $a['number'] <=> $b['number'];
    }

    if ($result === false) {
        throw new \Exception("Can't compare {$a['key']} and {$b['key']} due to insufficient data. First item: ".json_encode($a)." Second item: ".json_encode($b));
    }

    return $result;
};

uasort($items, $sort);

// Inject all entries that were manually placed.
do {
    $filterItemsWithoutReference = function($item) use ($itemsManualSort) {
        return isset($itemsManualSort[$item['after']]) === false;
    };
    
    $itemsToInsert = array_filter($itemsManualSort, $filterItemsWithoutReference); // extract items that are not referenced themselves
    foreach ($itemsToInsert as $key => $item) {
        $offset = array_search($item['after'], array_keys($items));
        if ($offset === false) {
            throw new \Exception("Predecessor item {$item['after']} not found for {$key}.");
        }

        $offset++;
        $items = array_slice($items, 0, $offset, true) + [$key => $item] + array_slice($items, $offset, null, true);
    }

    $itemsManualSort = array_diff_key($itemsManualSort, $itemsToInsert); // update list of remaining items to be processed
} while (count($itemsManualSort) > 0);

$runTime = microtime(true) - $startTime;

// Rendering
?><html>
<head>
    <style>
       .year {
           background-color: blue;
           color: white;
           font-weight: bold;
       }
       .item {
       }
    </style>
</head>
<body>
<?php

$runTime = round($runTime * 1000, 2);
$memory = round(memory_get_peak_usage(true) / 1000 / 1000, 2);
$memoryPeak = round(memory_get_usage(true) / 1000 / 1000, 2);

echo "Run time: {$runTime} ms<br />";
echo "Memory usage: {$memory} MB<br />";
echo "Memory peak usage: {$memoryPeak} MB<br /><br />";

$previousYear = false;
foreach ($items as $item) {
    $year = getYear($item['startDate'], $datePattern, $datePositions);
    if ($previousYear !== $year) {
        echo "<div class=\"year\">{$year}</div>\n";
    }
    
    echo '<div class="item">';
    echo "[{$item['package']->media}] ";
    echo $item['package']->symbol;
    
    if ($item['number'] === '--') {
        echo " \"{$item['parent']['title']}\"";
        if (empty($item['details']) === false) {
            echo " ({$item['details']})";
        }
        echo " <i>- {$item['title']}</i> (see primary entry in ".getYear($item['parent']['startDate'], $datePattern, $datePositions).")";
    } else {
        echo " {$item['number']}";
        echo " \"{$item['title']}\"";
    }
    
    if (empty($item['startStardate']) === false) {
        echo " - Stardate {$item['startStardate']}";
        if (empty($item['endStardate']) === false) {
            echo " to {$item['endStardate']}";
        }
    } elseif(preg_match($fullDatePattern, $item['startDate']) === 1) { // full date
        echo ' - ';
        $startDate = new \DateTime($item['startDate']);
        if(preg_match($fullDatePattern, $item['endDate']) === 1) {
            $endDate = new \DateTime($item['endDate']);
            if ($endDate->format('Y-m') === $startDate->format('Y-m')) { // same year & month
                echo $startDate->format('F j').'-'.$endDate->format('j');
            } elseif ($endDate->format('Y') === $startDate->format('Y')) { // just same year
                echo $startDate->format('F j').' to '.$endDate->format('F j');
            } else {
                echo $startDate->format('F j, Y').' to '.$endDate->format('F j, Y');
            }
        } elseif ($startDate->format('Y') == $year) {
            echo $startDate->format('F j');
        } else {
            echo $startDate->format('F j, Y');
        }
    } elseif(preg_match($monthDatePattern, $item['startDate']) === 1) { // year & month
        echo ' - ';
        echo (new \DateTime($item['startDate'].'-01'))->format('F'); //
    }
    
    if ($item['number'] !== '--' && empty($item['details']) === false) {
        echo "<i> - {$item['details']}</i>";
    }
    
    echo "</div>\n";
    
    $previousYear = $year;
}

function getYear(string $date, string $datePattern, array $datePositions):string
{
    $matches = [];
    preg_match($datePattern, $date, $matches);
    
    $date = '';
    
    if (empty($matches[$datePositions['about']]) === false) {
        $date .= 'C.';
    }
    
    $year = $matches[$datePositions['year']];
    if (empty($matches[$datePositions['bc']]) === false && $year >= 10000) {
        $year = number_format($year);
    }
    
    $date .= $year;
    
    if (empty($matches[$datePositions['bc']]) === false) {
        $date .= ' BC';
    }
    
    return $date;
}