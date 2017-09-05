<?php

namespace Gephart\ORM\Configuration;

use Gephart\Configuration\Configuration;

/**
 * ORM configuration
 *
 * @package Gephart\ORM
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.2
 */
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

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $orm = $configuration->get("orm");

        $this->orm = $orm;
        $this->directory = $configuration->getDirectory();
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function get(string $key)
    {
        return isset($this->orm[$key]) ? $this->orm[$key] : false;
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }
}