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

    /**
     * @var Executor
     */
    private $executor;

    public function __construct(Container $container, Connector $connector, SQLBuilder $builder, Executor $executor)
    {
        $this->container = $container;
        $this->connector = $connector;
        $this->builder = $builder;
        $this->executor = $executor;
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

    public function findBy(string $entity, array $find_by = [], array $params = [])
    {
        $query = $this->builder->select($entity, $find_by, $params);
        return $this->executor->select($entity, $query);
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
            throw $exception;
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
            throw $exception;
        }
    }
}