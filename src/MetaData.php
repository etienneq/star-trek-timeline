<?php
namespace EtienneQ\StarTrekTimeline;

/**
 * Value object representing a Package's meta data.
 */
class MetaData
{
    /**
     * @var string
     */
    protected $id = '';
    
    /**
     * @var string
     */
    public $title = '';
    
    /**
     * @var string
     */
    public $symbol = '';
    
    /**
     * @var string
     */
    public $media = '';

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId():string
    {
        return $this->id;
    }

}
