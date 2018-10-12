<?php

// Prototype
// Will be rewritten to proper OO code.

use League\Csv\Reader;
use EtienneQ\Stardate\Calculator;
use EtienneQ\StarTrekTimeline\RecursiveDirectoryScanner;
use EtienneQ\StarTrekTimeline\MetaDataFactory;

require_once __DIR__.'/vendor/autoload.php';

$resourcesDir = __DIR__.'/resources';

$calculator = new Calculator();

$datePattern = '/^([0-9]{4})(-([0-9]{2}))?(-([0-9]{2}))?$/';
$datePositions = [
    'year' => 1,
    'month' => 3,
    'day' => 5,
];

$tngEraSeries = [
    'tv/tng',
    'tv/ds9',
    'tv/voy',
];

$scanner = new RecursiveDirectoryScanner();

// Load meta data
$metaDataFiles = $scanner->getFiles($resourcesDir, 'json');
$metaDataFactory = new MetaDataFactory($metaDataFiles);

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
	    $record['package'] = $metaDataFactory->getMetaData($packageName);
	    
	    if (empty($record['number']) === true || $record['number'] === '--') {
	        $words = preg_split("/\s+/", trim(preg_replace('/[^a-z0-9]/i', ' ', $record['title'])));
	        $acronym = '';
	        foreach ($words as $word) {
	            $acronym .= $word[0];
	        }
	    } else {
	        $acronym = $record['number'];
	    }
	    
	    $record['key'] = $record['package']->id.'-'.$acronym;
		
	    // Overwrites startDate and endDate when stardate is given
	    if (in_array($record['package']->id, $tngEraSeries) === true) {
		    if (empty($record['startStardate']) === false) {
		        $record['startDate'] = $calculator->toGregorianDate($record['startStardate'])->format('Y-m-d');
		    }
		    if (empty($record['endStardate']) === false) {
		        $record['endDate'] = $calculator->toGregorianDate($record['endStardate'])->format('Y-m-d');
		    }
		}
		
		if (empty($record['startDate']) === true) {
		    throw new \Exception("Either start date must be set. Missing for {$record['key']}");
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
    
    // Sort by start stardate
	if (empty($a['startStardate']) === false && empty($b['startStardate']) === false) {
		$result = (float)$a['startStardate'] <=> (float)$b['startStardate'];
	}
	
	if ($result !== false && $result !== 0) { // compared but not equal
	    return $result;
	}
	
	// Sort by start date as exactly as possible
	if (empty($a['startDate']) === false && empty($b['startDate']) === false) {
	    $datePartsA = [];
	    $datePartsB = [];
	    
	    if (preg_match($datePattern, $a['startDate'], $datePartsA) !== 1) {
	        throw new \Exception("Invalid startdate given for {$a['key']}: {$a['startDate']}");
	    }
	    
	    if (preg_match($datePattern, $b['startDate'], $datePartsB) !== 1) {
	        throw new \Exception("Invalid startdate given for {$b['key']}: {$b['startDate']}");
	    }
	    
	    foreach ($datePositions as $position) {
	        if (isset($datePartsA[$position]) === true && isset($datePartsB[$position]) === true) { // both set
	            $result = $datePartsA[$position] <=> $datePartsB[$position];
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
	       (in_array($a['package']->id, $tngEraSeries) === true && in_array($b['package']->id, $tngEraSeries) === true) ||
	       $a['package']->id === $b['package']->id
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
	if ($a['package']->id === $b['package']->id) {
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

$previousYear = 0;
foreach ($items as $item) {
    $year = getYear($item['startDate']);
    if ($previousYear !== $year) {
        echo "<div class=\"year\">{$year}</div>";
    }
    
    echo '<div class="item">';
    echo "[{$item['package']->media}] ";
    echo $item['package']->symbol;
    
    if ($item['number'] === '--') {
        echo " \"{$item['parent']['title']}\"";
        if (empty($item['details']) === false) {
            echo " ({$item['details']})";
        }
        echo " <i>- {$item['title']}</i> (see primary entry in ".substr($item['parent']['startDate'], 0, 4).")";
    } else {
        echo " {$item['number']}";
        echo " \"{$item['title']}\"";
    }
    
    if (empty($item['startStardate']) === false) {
        echo " - Stardate {$item['startStardate']}";
        if (empty($item['endStardate']) === false) {
            echo " to {$item['endStardate']}";
        }
    } elseif(strlen($item['startDate']) === 10) { // full date
        echo ' - ';
        $startDate = new \DateTime($item['startDate']);
        if(strlen($item['endDate']) === 10) {
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
    } elseif(strlen($item['startDate']) === 7) { // year & month
        echo ' - ';
        echo (new \DateTime($item['startDate'].'-01'))->format('F');
    }
    
    if ($item['number'] !== '--' && empty($item['details']) === false) {
        echo "<i> - {$item['details']}</i>";
    }
    
    echo '</div>';
    
    $previousYear = $year;
}

function getYear(string $date):int
{
    if (strlen($date) === 4) {
        return (int)$date;
    }  else {
        return (int)substr($date, 0, 4);
    }
}