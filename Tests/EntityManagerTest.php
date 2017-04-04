<?php

include_once __DIR__ . "/../vendor/autoload.php";
include_once __DIR__ . "/Entity/Entity.php";
include_once __DIR__ . "/Repository/EntityRepository.php";

class EntityManagerTest extends \PHPUnit\Framework\TestCase
{

    private $container;
    private $entity_manager;
    private $entity;
    private $pdo;

    public function setUp()
    {
        $container = new \Gephart\DependencyInjection\Container();
        $configuration = $container->get(\Gephart\Configuration\Configuration::class);
        $configuration->setDirectory(__DIR__ . "/config/");

        $this->container = $container;
        $this->entity_manager = $container->get(\Gephart\ORM\EntityManager::class);
        $this->entity = Entity::class;
        $this->pdo = $container->get(\Gephart\ORM\Connector::class)->getPdo();
    }

    public function testCreateTable()
    {
        $this->entity_manager->deleteTable($this->entity);
        $this->entity_manager->createTable($this->entity);

        $ok = false;
        try {
            $this->pdo->query("SELECT 1 FROM entity LIMIT 1;");
            $this->pdo->query("SELECT 1 FROM entity_translation LIMIT 1;");

            $ok = true;
        } catch (PDOException $exception) {}

        $this->assertTrue($ok);
    }

}