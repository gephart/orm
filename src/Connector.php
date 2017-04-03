<?php

namespace Gephart\ORM;

use Gephart\ORM\Configuration\ORMConfiguration;

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

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

}