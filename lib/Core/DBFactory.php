<?php

namespace Lib\Core;

use ReflectionClass;

class DBFactory
{
    private $dbClass;

    private $dbConfig;

    public function __construct($dbClass, $dbConfig)
    {
        $this->dbClass = $dbClass;
        $this->dbConfig = $dbConfig;
    }

    public function create()
    {
        $reflection = new ReflectionClass($this->dbClass);
        $object = $reflection->newInstance($this->dbConfig);
        return $object;
    }
}
