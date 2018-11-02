<?php
// Follows timeline design of "Voyages of Imaginations"

use EtienneQ\StarTrekTimeline\DateFormat;
use EtienneQ\StarTrekTimeline\Timeline;
use EtienneQ\StarTrekTimeline\Data\ItemsFile;

require_once __DIR__.'/vendor/autoload.php';

$strictMode = false;
$displayErrors = true;

$startTime = microtime(true);
$timeline = new Timeline($strictMode);
$items = $timeline->findAll();
$runTime = microtime(true) - $startTime;

$runTime = round($runTime * 1000, 2);
$memory = round(memory_get_peak_usage(true) / 1000 / 1000, 2);
$memoryPeak = round(memory_get_usage(true) / 1000 / 1000, 2);

if ($strictMode === false && $displayErrors === true) {
    displayErrors($timeline->getErrors());
}

function displayErrors(array $errors) {
    if (empty($errors) === true) {
        return;
    }
    
    echo "<ul>\n";
    foreach ($errors as $error) {
        echo "<li>\n";
        if ($error instanceof \Throwable) {
            echo '<b>'.get_class($error)."</b><br />\n";
            echo $error->getMessage();
            if (empty($error->getPrevious()) === false) {
                displayErrors([$error->getPrevious()]);
            }
        } elseif (is_array($error) === true) {
            displayErrors($error);
        } else {
            var_dump($error);
        }
        echo "</li>\n";
    }
    echo "</ul>\n";
}

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

echo "Run time: {$runTime} ms<br />";
echo "Memory usage: {$memory} MB (peak: {$memoryPeak} MB)<br /><br />";

$previousYear = false;
foreach ($items as $item) {
    $year = DateFormat::renderYear($item->getStartDate());
    if ($previousYear !== $year) {
        echo "<div class=\"year\">{$year}</div>\n";
    }
    
    echo '<div class="item">';
    echo "[{$item->getMetaData()->media}] {$item->getMetaData()->symbol}";
    
    if (empty($item->number) === false) {
        echo " {$item->number}";
    }
    
    echo " \"{$item->getTitle()}\"";
    
    if (empty($item->description) === false) {
        echo " ({$item->description})";
    }
    
    if (empty($item->sections) === false) {
        echo " <i>- {$item->sections}</i>";
        if ($item->hasParent() === true) {
            echo " (see primary entry in ".DateFormat::renderYear($item->getParent()->getStartDate()).")";
        }
    }
    
    if (empty($item->getStartStardate()) === false) {
        echo ' - Stardate '.number_format($item->getStartStardate(), 1, '.', '');
        if (empty($item->getEndStardate()) === false) {
            echo ' to '.number_format($item->getEndStardate(), 1, '.', '');
        }
    }
    if(DateFormat::isValidFullDate($item->getStartDate()) === true) {
        echo ' - ';
        $startDate = new \DateTime($item->getStartDate());
        if(DateFormat::isValidFullDate($item->getEndDate()) === true) {
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
    } elseif(DateFormat::isValidYearMonth($item->getStartDate()) === true) {
        echo ' - ';
        echo (new \DateTime($item->getStartDate().'-01'))->format('F');
    }
    
    if (empty($item->historiansNote) === false) {
        echo "<i> - {$item->historiansNote}</i>";
    }
    
    echo "</div>\n";
    
    $previousYear = $year;
}
