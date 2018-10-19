<?php
namespace EtienneQ\StarTrekTimeline\Data;

class DataFile
{
    public const FILE_ENDING = 'csv';
    
    // CSV column names
    public const NUMBER = 'number';
    public const TITLE = 'title';
    public const START_DATE = 'startDate';
    public const END_DATE = 'endDate';
    public const START_STARDATE = 'startStardate';
    public const END_STARDATE = 'endStardate';
    public const PUBLICATION_DATE = 'publicationDate';
    public const PREDECESSOR_ID = 'after';
    public const DESCRIPTION = 'details';
//     public const HISTORIANS_NOTE = 'historiansNote';
//     public const SECTIONS = 'sections';

    /**
     * Designates a child entry.
     * @var string
     */
    public const NUMBER_CHILD = '--';
}
