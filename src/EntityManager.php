<?php

namespace Gephart\ORM;

use Gephart\DependencyInjection\Container;

class EntityManager
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var SQLBuilder
     */
    private $builder;

    public function __construct(Container $container, Connector $connector, SQLBuilder $builder)
    {
        $this->container = $container;
        $this->connector = $connector;
        $this->builder = $builder;
    }

    public function getRepository(string $repository)
    {
        return $this->container->get($repository);
    }

    public function createTable(string $entity)
    {
        $query = $this->builder->createTable($entity);
        $pdo = $this->connector->getPdo();
        $pdo->exec($query);
    }

    public function deleteTable(string $entity)
    {
        $query = $this->builder->deleteTable($entity);
        $pdo = $this->connector->getPdo();
        $pdo->exec($query);
    }

    public function save($entity)
    {
        try {
            $queries = $this->builder->update($entity);
            $pdo = $this->connector->getPdo();

            foreach ($queries as $key=>$query) {
                $pdo->query($query);

                if ($key == 0 && !$entity->getId()) {
                    $entity->setId($pdo->lastInsertId());
                }
            }

        } catch (\PDOException $exception) {
            throw new $exception;
        }
    }

    public function remove($entity)
    {
        $query = $this->builder->delete($entity);

        $pdo = $this->connector->getPdo();

        try {
            $pdo->exec($query);

            if (method_exists($entity, "setId")) {
                $entity->setId(0);
            }

            return true;
        } catch (\PDOException $exception) {
            throw new $exception;
        }
    }
}