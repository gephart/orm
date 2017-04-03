<?php

namespace Gephart\ORM\Configuration;

use Gephart\Configuration\Configuration;

class ORMConfiguration
{
    /**
     * @var array
     */
    private $orm;

    /**
     * @var string
     */
    private $directory;

    public function __construct(Configuration $configuration)
    {
        $orm = $configuration->get("orm");

        $this->orm = $orm;
        $this->directory = $configuration->getDirectory();
    }

    public function get(string $key)
    {
        return isset($this->orm[$key]) ? $this->orm[$key] : false;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}