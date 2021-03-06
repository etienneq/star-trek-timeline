<?php
namespace EtienneQ\StarTrekTimeline\Data;

class ItemsFile
{
    public const FILE_ENDING = 'csv';
    
    // CSV column names
    public const NUMBER = 'number';
    public const TITLE = 'title';
    public const SECTIONS = 'sections';
    public const DESCRIPTION = 'description';
    public const START_DATE = 'startDate';
    public const END_DATE = 'endDate';
    public const START_STARDATE = 'startStardate';
    public const END_STARDATE = 'endStardate';
    public const HISTORIANS_NOTE = 'historiansNote';
    public const PUBLICATION_DATE = 'publicationDate';
    public const PREDECESSOR_ID = 'predecessorId';

    public const DATA_FILE_HEADERS = [
        self::NUMBER,
        self::TITLE,
        self::SECTIONS,
        self::DESCRIPTION,
        self::START_DATE,
        self::END_DATE,
        self::START_STARDATE,
        self::END_STARDATE,
        self::HISTORIANS_NOTE,
        self::PUBLICATION_DATE,
        self::PREDECESSOR_ID,
    ];
    
    /**
     * Designates a child entry.
     * @var string
     */
    public const NUMBER_CHILD = '--';
}
