<?php
namespace EtienneQ\StarTrekTimeline;

class RecursiveDirectoryScanner
{
    public function getFiles(string $dir, string $ending):array
    {
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
            throw new \Exception("Error scanning directory {$dir}");
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
                    throw new \Exception("File {$fullPath} is not readable.");
                }
                
                $foundFiles[] = $fullPath;
            } elseif (is_dir($fullPath) === true) {
                $foundInSubdirectory = array_merge($foundInSubdirectory, $this->getFilesRecursively($fullPath, $ending));
            }
        }
        
        return array_merge($foundFiles, $foundInSubdirectory);
    }
}
