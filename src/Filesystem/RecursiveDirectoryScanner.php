<?php
namespace EtienneQ\StarTrekTimeline\Filesystem;

class RecursiveDirectoryScanner
{
    /**
     * @var bool
     */
    protected $strictMode = false;
    
    /**
     * @var array
     */
    protected $errors = [];
    
    /**
     *
     * @param bool $strictMode abort loading if true, skip erroneous files if false
     */
    public function __construct(bool $strictMode = true)
    {
        $this->strictMode = $strictMode;
    }
    
    public function getErrors():array
    {
        return $this->errors;
    }

    public function getFiles(string $dir, string $ending):array
    {
        // Reset
        $this->errors = [];
        
        $files = [];
        foreach ($this->getFilesRecursively($dir, $ending) as $file) {
            $key = trim(substr($file, strlen($dir)), '/\\');
            $files[$key] = $file;
        }

        return $files;
    }
    
    protected function getFilesRecursively(string $dir, string $ending):array
    {
        $listing = scandir($dir);
        if ($listing === false) {
            $exception = new DirectoryException("Error scanning directory {$dir}");
            if ($this->strictMode === true) {
                throw $exception;
            } else {
                $this->errors[] = $exception;
                return [];
            }
        }
        
        $foundFiles = [];
        $foundInSubdirectory = [];
       
        foreach ($listing as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            
            $fullPath = $dir.'/'.$name;
            $matches = [];
            if (is_file($fullPath) === true && preg_match("/^.+\.{$ending}$/i", $fullPath, $matches) === 1) {
                if (is_readable($fullPath) === false) {
                    $exception = new FileException("File {$fullPath} is not readable.");
                    if ($this->strictMode === true) {
                        throw $exception;
                    } else {
                        $this->errors[] = $exception;
                        continue;
                    }
                }
                
                $foundFiles[] = $fullPath;
            } elseif (is_dir($fullPath) === true) {
                $foundInSubdirectory = array_merge($foundInSubdirectory, $this->getFilesRecursively($fullPath, $ending));
            }
        }
        
        return array_merge($foundFiles, $foundInSubdirectory);
    }
}
