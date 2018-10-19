<?php
namespace EtienneQ\StarTrekTimeline\Data;

use EtienneQ\Stardate\Calculator;

/**
 * Factory to create an item.
 */
class ItemFactory
{
    public function createItem(array $attributes, MetaData $metaData):Item
    {
        $id = $this->generateItemId($attributes[ItemsFile::NUMBER] ?? '', $attributes[ItemsFile::TITLE], $metaData);
        $item = new Item(
            $id,
            $attributes[ItemsFile::TITLE] ?? '',
            $attributes[ItemsFile::START_DATE] ?? '',
            new Calculator()
        );

        $item->setMetaData($metaData);
        $item->number = $attributes[ItemsFile::NUMBER] ?? '';
        $item->setEndDate($attributes[ItemsFile::END_DATE] ?? '');
        $item->publicationDate = $attributes[ItemsFile::PUBLICATION_DATE] ?? '';
        $item->predecessorId = $attributes[ItemsFile::PREDECESSOR_ID] ?? '';
        $item->description = $attributes[ItemsFile::DESCRIPTION] ?? '';
        
        if (empty($attributes[ItemsFile::START_STARDATE]) === false) {
            $item->setStartStardate($attributes[ItemsFile::START_STARDATE]);
        }
        
        if (empty($attributes[ItemsFile::END_STARDATE]) === false) {
            $item->setEndStardate($attributes[ItemsFile::END_STARDATE]);
        }

        return $item;
    }
    
    protected function generateItemId(string $number, string $title, MetaData $metaData):string
    {
        if (empty($number) === true || $number === ItemsFile::NUMBER_CHILD) {
            $words = preg_split("/\s+/", trim(preg_replace('/[^a-z0-9]/i', ' ', $title)));
            $itemHash = '';
            foreach ($words as $word) {
                $itemHash .= $word[0];
            }
            $itemHash = strtoupper($itemHash);
        } else {
            $itemHash = $number;
        }
        
        return $metaData->getId().'-'.$itemHash;
    }
}
