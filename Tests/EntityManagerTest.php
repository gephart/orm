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

    public function setUp(): void
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

    public function testInsert()
    {
        $entity = new Entity();
        $entity->setTitle("tes'ting");

        $this->entity_manager->save($entity);

        $this->assertTrue($entity->getId() > 0);
    }

    public function testSelect()
    {
        $repository = $this->entity_manager->getRepository(EntityRepository::class);
        $result = $repository->findBy(["title = %1", "tes'ting"]);

        $this->assertTrue(count($result) == 1);
    }

    public function testUpdate()
    {
        $repository = $this->entity_manager->getRepository(EntityRepository::class);
        $result = $repository->findBy(["title = %1", "tes'ting"]);

        $entity = $result[0];
        $entity->setTitle("update_testing");
        $this->entity_manager->save($entity);

        $result = $repository->findBy(["title = %1", "tes'ting"]);
        $this->assertTrue(count($result) == 0);

        $result = $repository->findBy(["title = %1", "update_testing"]);
        $this->assertTrue(count($result) == 1);
    }

    public function testRemove()
    {
        $repository = $this->entity_manager->getRepository(EntityRepository::class);
        $result = $repository->findBy(["title = %1", "update_testing"]);
        $entity = $result[0];

        $this->entity_manager->remove($entity);

        $result = $repository->findBy(["title = %1", "tes'ting"]);

        $this->assertTrue(count($result) == 0);
    }

    public function testDeleteTable()
    {
        $this->entity_manager->deleteTable($this->entity);

        $ok = false;
        try {
            $this->pdo->query("SELECT 1 FROM entity LIMIT 1;");
            $this->pdo->query("SELECT 1 FROM entity_translation LIMIT 1;");
        } catch (PDOException $exception) {
            $ok = true;
        }

        $this->assertTrue($ok);
    }


}