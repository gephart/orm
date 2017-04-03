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

    public function getTable(string $entity): string
    {
        return $this->builder->createTable($entity);
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
        $delete = 'DELETE FROM news WHERE id = :id LIMIT 1';

        $pdo = $this->connector->getPdo();

        try {
            if ($entity->getId()) {
                $sth = $pdo->prepare($delete);
                $sth->execute([
                    ':id' => $entity->getId(),
                ]);
                return true;
            }

            return false;
        } catch (\PDOException $exception) {
            throw new $exception;
        }
    }
}