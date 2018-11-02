<?php
namespace EtienneQ\StarTrekTimeline\Data;

class RecordException extends \RuntimeException
{
    public function __construct(string $id, string $message = null, int $code = null, \Throwable $previous = null) {
        parent::__construct($id.': '.$message, $code, $previous);
    }
}
