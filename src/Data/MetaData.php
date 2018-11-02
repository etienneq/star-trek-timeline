<?php
namespace EtienneQ\StarTrekTimeline\Data;

/**
 * Value object representing a Package's meta data.
 */
class MetaData
{
    protected const TOS_ERA_PACKAGES = [
        '/^tv\/tas\/season[1-2]$/',
        '/^tv\/tos\/season[1-3]$/',
    ];
    
    protected const TNG_ERA_PACKAGES = [
        '/^tv\/tng\/season[1-7]$/',
        '/^tv\/ds9\/season[1-7]$/',
        '/^tv\/voy\/season[1-7]$/',
    ];
    
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
    
    /**
     * Tells wether package is one of different TOS era TV series's seasons.
     * @return bool
     */
    public function isTosEraTvSeries():bool
    {
        foreach (self::TOS_ERA_PACKAGES as $pattern) {
            if (preg_match($pattern, $this->id) === 1) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Tells wether package is one of different TNG era TV series's seasons.
     * @return bool
     */
    public function isTngEraTvSeries():bool
    {
        foreach (self::TNG_ERA_PACKAGES as $pattern) {
            if (preg_match($pattern, $this->id) === 1) {
                return true;
            }
        }
        
        return false;
    }
}
