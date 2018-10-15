<?php
namespace EtienneQ\StarTrekTimeline\Data;

use EtienneQ\Stardate\Calculator;

class ItemFactory
{
    public function createItem(array $attributes, Package $package):Item
    {
        $id = $this->generateItemId($attributes['number'] ?? '', $attributes['title'], $package);
        $item = new Item(
            $id,
            $attributes['title'] ?? '',
            $attributes['startDate'] ?? '',
            new Calculator()
        );

        $item->setPackage($package);
        $item->number = $attributes['number'] ?? '';
        $item->setEndDate($attributes['endDate'] ?? '');
        $item->publicationDate = $attributes['publicationDate'] ?? '';
        $item->after = $attributes['after'] ?? '';
        $item->details = $attributes['details'] ?? '';
        
        if (empty($attributes['startStardate']) === false) {
            $item->setStartStardate($attributes['startStardate']);
        }
        
        if (empty($attributes['endStardate']) === false) {
            $item->setEndStardate($attributes['endStardate']);
        }

        return $item;
    }
    
    protected function generateItemId(string $number, string $title, Package $package):string
    {
        if (empty($number) === true || $number === '--') {
            $words = preg_split("/\s+/", trim(preg_replace('/[^a-z0-9]/i', ' ', $title)));
            $itemHash = '';
            foreach ($words as $word) {
                $itemHash .= $word[0];
            }
            $itemHash = strtoupper($itemHash);
        } else {
            $itemHash = $number;
        }
        
        return $package->getId().'-'.$itemHash;
    }
}
