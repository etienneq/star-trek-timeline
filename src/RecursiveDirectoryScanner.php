<?php
namespace EtienneQ\StarTrekTimeline;

class RecursiveDirectoryScanner
{
    public function getFiles(string $dir, string $ending):array
    {
        $listing = scandir($dir);
        if ($listing === false) {
            throw new \Exception("Error scanning directory {$dir}");
        }
        
        $foundFiles = [];
        
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
                $foundFiles = array_merge($foundFiles, $this->getFiles($fullPath, $ending));
            }
        }
        
        return $foundFiles;
    }
}
