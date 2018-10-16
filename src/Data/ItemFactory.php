<?php
namespace EtienneQ\StarTrekTimeline\Data;

use EtienneQ\Stardate\Calculator;

class ItemFactory
{
    public function createItem(array $attributes, Package $package):Item
    {
        $id = $this->generateItemId($attributes[DataFile::NUMBER] ?? '', $attributes[DataFile::TITLE], $package);
        $item = new Item(
            $id,
            $attributes[DataFile::TITLE] ?? '',
            $attributes[DataFile::START_DATE] ?? '',
            new Calculator()
        );

        $item->setPackage($package);
        $item->number = $attributes[DataFile::NUMBER] ?? '';
        $item->setEndDate($attributes[DataFile::END_DATE] ?? '');
        $item->publicationDate = $attributes[DataFile::PUBLICATION_DATE] ?? '';
        $item->after = $attributes[DataFile::PREDECESSOR_ID] ?? '';
        $item->details = $attributes[DataFile::DESCRIPTION] ?? '';
        
        if (empty($attributes[DataFile::START_STARDATE]) === false) {
            $item->setStartStardate($attributes[DataFile::START_STARDATE]);
        }
        
        if (empty($attributes[DataFile::END_STARDATE]) === false) {
            $item->setEndStardate($attributes[DataFile::END_STARDATE]);
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
