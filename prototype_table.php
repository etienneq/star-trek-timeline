<?php

// Displays all item attributes in a table

use EtienneQ\StarTrekTimeline\DateFormat;
use EtienneQ\StarTrekTimeline\Timeline;
use EtienneQ\StarTrekTimeline\Data\ItemsFile;

require_once __DIR__.'/vendor/autoload.php';

$startTime = microtime(true);
$items = (new Timeline())->findAll();
$runTime = microtime(true) - $startTime;

$runTime = round($runTime * 1000, 2);
$memory = round(memory_get_peak_usage(true) / 1000 / 1000, 2);
$memoryPeak = round(memory_get_usage(true) / 1000 / 1000, 2);

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

?>
<table border="1">
<tr>
	<th>Start Date</th>
	<th>ID</th>
	<th>Number</th>
	<th>Title</th>
	<th>Sections</th>
	<th>End Date</th>
	<th>Start Stardate</th>
	<th>End Stardate</th>
	<th>Publication Date</th>
	<th>Description</th>
	<th>Historian's Note</th>
	<th>Predecessor ID</th>
	<th>Meta Data</th>
	<th>Parent</th>
</tr>
<?php
foreach ($items as $item) {
    echo '<tr>';
    echo '<td>'.$item->getStartDate().'</td>';
    echo '<td>'.$item->getId().'</td>';
    echo '<td>'.$item->number.'</td>';
    echo '<td>'.$item->getTitle().'</td>';
    echo '<td>'.$item->sections.'</td>';
    echo '<td>'.$item->getEndDate().'</td>';
    echo '<td>'.$item->getStartStardate().'</td>';
    echo '<td>'.$item->getEndStardate().'</td>';
    echo '<td>'.$item->getPublicationDate().'</td>';
    echo '<td>'.$item->description.'</td>';
    echo '<td>'.$item->historiansNote.'</td>';
    echo '<td>'.$item->predecessorId.'</td>';
    echo '<td>'.$item->getMetaData()->symbol.' - '.$item->getMetaData()->title.'</td>';
    if (empty($item->getParent()) === false) {
        echo '<td>'.$item->getParent()->getId().'</td>';
    } else {
        echo '<td>-</td>';
    }
    echo '</tr>';
}
?>
</table>