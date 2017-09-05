<?php

namespace Gephart\ORM;

use Gephart\ORM\Configuration\ORMConfiguration;

/**
 * Connector
 *
 * @package Gephart\ORM
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.2
 */
class Connector
{

    /**
     * @var ORMConfiguration
     */
    private $database_configuration;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @param ORMConfiguration $database_configuration
     */
    public function __construct(ORMConfiguration $database_configuration)
    {
        $this->database_configuration = $database_configuration;

        $this->pdo = new \PDO(
            'mysql:host='.$database_configuration->get("hostname").';port='.$database_configuration->get("port").';dbname='.$database_configuration->get("database").'',
            $database_configuration->get("username"),
            $database_configuration->get("password")
        );
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->query("SET NAMES utf8;");
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

}