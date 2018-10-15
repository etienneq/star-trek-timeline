<?php

// Prototype
// Will be rewritten to proper OO code.

use League\Csv\Reader;
use EtienneQ\StarTrekTimeline\Data\ItemFactory;
use EtienneQ\StarTrekTimeline\Data\PackageFactory;
use EtienneQ\StarTrekTimeline\RecursiveDirectoryScanner;
use EtienneQ\StarTrekTimeline\Sort\AutomatedSort;
use EtienneQ\StarTrekTimeline\Sort\ManualSort;
use EtienneQ\StarTrekTimeline\Sort\Comparator\StartStardate;
use EtienneQ\StarTrekTimeline\Sort\Comparator\StartDate;
use EtienneQ\StarTrekTimeline\Sort\Comparator\PublicationDate;
use EtienneQ\StarTrekTimeline\Sort\Comparator\Number;
use EtienneQ\StarTrekTimeline\DateFormat;

require_once __DIR__.'/vendor/autoload.php';

$startTime = microtime(true);

$resourcesDir = __DIR__.'/resources';

$scanner = new RecursiveDirectoryScanner();

// Load meta data for all packages
$metaDataFiles = $scanner->getFiles($resourcesDir, 'json');
$packageFactory = new PackageFactory($metaDataFiles);

// Load all data files
$dataFiles = $scanner->getFiles($resourcesDir, 'csv');

$fileHeaders = ['number', 'title', 'startDate', 'endDate', 'startStardate', 'endStardate', 'publicationDate', 'after', 'details'];

$itemFactory = new ItemFactory();

$automatedSort = new AutomatedSort();
$automatedSort->addCompator(new StartStardate());
$automatedSort->addCompator(new StartDate());
$automatedSort->addCompator(new PublicationDate());
$automatedSort->addCompator(new Number());

$manualSort = new ManualSort();

foreach ($dataFiles as $simpleFileName => $file) {
    $reader = Reader::createFromPath($file, 'r');
    
    $reader->setHeaderOffset(0);
    $headers = $reader->getHeader(); 
    if ($headers != $fileHeaders) {
        throw new \Exception("Headers in {$simpleFileName} do not match expectations.");
    }
    
    $headers = array_merge(['package', 'key'], $headers);

    $lastParent = null;

    foreach($reader->getRecords() as $record) {
        $item = $itemFactory->createItem($record, $packageFactory->getPackage($simpleFileName));

        if ($item->number  !== '--') {
            $lastParent = $item;
        } else {
            if ($lastParent === null) {
                throw new \Exception("Parent record not found for {$item->getId()}.");
            }
            
            $item->setParent($lastParent);
        } 
        
        if (empty($item->after) === true) {
            $automatedSort->addItem($item);
        } else {
            $manualSort->addItem($item);
        }
    }
}

$items = $automatedSort->sort();
$manualSort->injectInfo($items);

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
    $year = DateFormat::getYear($item->getStartDate());
    if ($previousYear !== $year) {
        echo "<div class=\"year\">{$year}</div>\n";
    }
    
    echo '<div class="item">';
    echo "[{$item->getPackage()->media}] ";
    echo $item->getPackage()->symbol;
    
    if ($item->number === '--') {
        echo " \"{$item->getParent()->getTitle()}\"";
        if (empty($item->details) === false) {
            echo " ({$item->details})";
        }
        echo " <i>- {$item->getTitle()}</i> (see primary entry in ".DateFormat::getYear($item->getParent()->getStartDate()).")";
    } else {
        echo " {$item->number}";
        echo " \"{$item->getTitle()}\"";
    }
    
    if (empty($item->getStartStardate()) === false) {
        echo ' - Stardate '.number_format($item->getStartStardate(), 1, '.', '');
        if (empty($item->getEndStardate()) === false) {
            echo ' to '.number_format($item->getEndStardate(), 1, '.', '');
        }
    } elseif(preg_match(DateFormat::PATTERN_FULL_DATE, $item->getStartDate()) === 1) {
        echo ' - ';
        $startDate = new \DateTime($item->getStartDate());
        if(preg_match(DateFormat::PATTERN_FULL_DATE, $item->getEndDate()) === 1) {
            $endDate = new \DateTime($item->getEndDate());
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
    } elseif(preg_match(DateFormat::PATTERN_YEAR_MONTH, $item->getStartDate()) === 1) {
        echo ' - ';
        echo (new \DateTime($item->getStartDate().'-01'))->format('F'); //
    }
    
    if ($item->number !== '--' && empty($item->details) === false) {
        echo "<i> - {$item->details}</i>";
    }
    
    echo "</div>\n";
    
    $previousYear = $year;
}
