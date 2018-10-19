<?php
namespace EtienneQ\StarTrekTimeline\Data;

class MetaDataFile
{
    public const FILE_ENDING = 'ini';
    public const GENERAL_FILE_NAME = 'meta.'.self::FILE_ENDING;
    
    // CSV column names
    public const TITLE = 'title';
    public const SYMBOL = 'symbol';
    public const MEDIA = 'media';
}
