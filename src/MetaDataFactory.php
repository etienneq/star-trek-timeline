<?php
namespace EtienneQ\StarTrekTimeline;

class MetaDataFactory
{
    protected const GENERAL_META_FILENAME = 'meta.json';
    
    /**
     * @var array
     */
    protected $files = [];
    
    /**
     * @var MetaData[]
     */
    protected $metaData = [];
    
    public function __construct(array $files) {
        $this->files = $files;
        uksort($this->files, [$this, 'sortGeneralMetaFilesFirst']);
    }
    
    public function getMetaData(string $packageName):MetaData
    {
        if (isset($this->metaData[$packageName]) === false) {
            $attributes = $this->loadAttributes($packageName);
            
            $metaData = new MetaData($packageName);
            $metaData->title = $attributes['title'] ?? '';
            $metaData->symbol = $attributes['symbol'] ?? '';
            $metaData->media = $attributes['media'] ?? '';
            
            $this->metaData[$packageName] = $metaData;
        }
        
        return $this->metaData[$packageName];
    }
    
    protected function sortGeneralMetaFilesFirst($file1, $file2):int
    {
        $path1 = pathinfo($file1, PATHINFO_DIRNAME);
        $path2 = pathinfo($file2, PATHINFO_DIRNAME);
        
        $filename1 = pathinfo($file1, PATHINFO_BASENAME);
        $filename2 = pathinfo($file2, PATHINFO_BASENAME);
        
        // Put general meta data files always first if both files are in same directory
        if ($path1 === $path2) {
            if ($filename1 === self::GENERAL_META_FILENAME) {
                return -1;
            } elseif ($filename2 === self::GENERAL_META_FILENAME) {
                return -1;
            }
        }
        
        return 0; // Leave original sort order unchanged
    }
    
    protected function loadAttributes(string $packageName):array
    {
        $files = $this->getFiles($packageName);
        if (count($files) === 0) {
            throw new \Exception("No meta data found for package {$packageName}.");
        }

        $attributes = [];
        foreach ($files as $file) {
            $attributes = array_merge($attributes, $this->loadAttributesFromFile($this->files[$file]));
        }
        
        return $attributes;
    }
    
    protected function getFiles(string $packageName):array
    {
        $files = [];
        foreach (array_keys($this->files) as $file) {
            $pathInfo = pathinfo($file);
            // A parent's general meta data or meta data for this specific package
            if ((strpos($packageName, $pathInfo['dirname']) === 0 && $pathInfo['basename'] === self::GENERAL_META_FILENAME) ||
                $file === $packageName.'.json'
            ) {
                $files[] = $file;
            }
            
        }
        
        return $files;
    }
    
    protected function loadAttributesFromFile(string $filename):array
    {
        $content = file_get_contents($filename);
        if ($content === false) {
            throw new \Exception("Could not read from meta data file {$filename}.");
        }
        
        return json_decode($content, true);
    }
}
