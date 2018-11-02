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
        $id = $this->generateItemId(
            $attributes[ItemsFile::NUMBER] ?? '',
            $attributes[ItemsFile::TITLE] ?? '',
            $attributes[ItemsFile::SECTIONS] ?? '',
            $metaData
            );
        $item = new Item(
            $id,
            $attributes[ItemsFile::START_DATE] ?? '',
            new Calculator()
        );

        $item->setMetaData($metaData);
        $item->number = $attributes[ItemsFile::NUMBER] ?? '';
        $item->title = $attributes[ItemsFile::TITLE] ?? '';
        $item->predecessorId = $attributes[ItemsFile::PREDECESSOR_ID] ?? '';
        $item->description = $attributes[ItemsFile::DESCRIPTION] ?? '';
        $item->historiansNote = $attributes[ItemsFile::HISTORIANS_NOTE] ?? '';
        $item->sections = $attributes[ItemsFile::SECTIONS] ?? '';
        
        if (empty($attributes[ItemsFile::END_DATE]) === false) {
            $item->setEndDate($attributes[ItemsFile::END_DATE]);
        }
        
        if (empty($attributes[ItemsFile::START_STARDATE]) === false) {
            $item->setStartStardate($attributes[ItemsFile::START_STARDATE]);
        }
        
        if (empty($attributes[ItemsFile::END_STARDATE]) === false) {
            $item->setEndStardate($attributes[ItemsFile::END_STARDATE]);
        }
        
        if (empty($attributes[ItemsFile::PUBLICATION_DATE]) === false) {
            $item->setPublicationDate($attributes[ItemsFile::PUBLICATION_DATE]);
        }

        return $item;
    }
    
    protected function generateItemId(string $number, string $title, string $sections, MetaData $metaData):string
    {
        if (empty($number) === true || $number === ItemsFile::NUMBER_CHILD) {
            if (empty($title) === false) {
                $acronymSource = $title;
            } elseif (empty($sections) === false) {
                $acronymSource = $sections;
            } else {
                throw new ItemException('Either title or sections attribute must be set.');
            }
            
            $words = preg_split("/\s+/", trim(preg_replace('/[^a-z0-9]/i', ' ', $acronymSource)));
            $itemHash = '';
            foreach ($words as $word) {
                // Preserve Roman numerals
                if (preg_match('/^[IVXLCDM]+$/', $word)) {
                    $itemHash .= $word;
                } else {
                    $itemHash .= $word[0];
                }
            }
            $itemHash = strtoupper($itemHash);
        } else {
            $itemHash = $number;
        }
        
        return $metaData->getId().'-'.$itemHash;
    }
}
