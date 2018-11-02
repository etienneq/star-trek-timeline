<?php
namespace EtienneQ\StarTrekTimeline\Data;

/**
 * Factory to create a meta data object.
 */
class MetaDataFactory
{
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
    
    public function getMetaData(string $filename):MetaData
    {
        $packageName = $this->resolvePackageName($filename);
        
        if (isset($this->metaData[$packageName]) === false) {
            $attributes = $this->loadAttributes($packageName);
            
            $metaData = new MetaData($packageName);
            $metaData->title = $attributes[MetaDataFile::TITLE] ?? '';
            $metaData->symbol = $attributes[MetaDataFile::SYMBOL] ?? '';
            $metaData->media = $attributes[MetaDataFile::MEDIA] ?? '';
            
            $this->metaData[$packageName] = $metaData;
        }
        
        return $this->metaData[$packageName];
    }
    
    protected function resolvePackageName(string $filename):string
    {
        $pathInfo = pathinfo($filename);
        return $pathInfo['dirname'].'/'.$pathInfo['filename'];
    }
    
    protected function sortGeneralMetaFilesFirst($file1, $file2):int
    {
        $path1 = pathinfo($file1, PATHINFO_DIRNAME);
        $path2 = pathinfo($file2, PATHINFO_DIRNAME);
        
        $filename1 = pathinfo($file1, PATHINFO_BASENAME);
        $filename2 = pathinfo($file2, PATHINFO_BASENAME);
        
        // Put general meta data files always first if both files are in same directory
        if ($path1 === $path2) {
            if ($filename1 === MetaDataFile::GENERAL_FILE_NAME) {
                return -1;
            } elseif ($filename2 === MetaDataFile::GENERAL_FILE_NAME) {
                return -1;
            }
        }
        
        return $path1 <=> $path2;
    }
    
    protected function loadAttributes(string $packageName):array
    {
        $files = $this->getFiles($packageName);
        if (count($files) === 0) {
            throw new MetaDataException("No meta data found for package {$packageName}.");
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
            if ((strpos($packageName, $pathInfo['dirname']) === 0 && $pathInfo['basename'] === MetaDataFile::GENERAL_FILE_NAME) ||
                $file === $packageName.'.'.MetaDataFile::FILE_ENDING
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
            throw new MetaDataException("Could not read from meta data file {$filename}.");
        }
        
        $attributes = parse_ini_file($filename);
        if ($attributes === false) {
            throw new MetaDataException("Could not parse content of meta data file {$filename}.");
        }
        
        return $attributes;
    }
}
