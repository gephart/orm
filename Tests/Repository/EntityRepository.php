<?php

use Gephart\ORM\Executor;
use Gephart\ORM\SQLBuilder;

class EntityRepository
{

    /**
     * @var Executor
     */
    private $executor;

    /**
     * @var SQLBuilder
     */
    private $sql_builder;

    public function __construct(Executor $executor, SQLBuilder $sql_builder)
    {
        $this->executor = $executor;
        $this->sql_builder = $sql_builder;
        $this->entity_class = Entity::class;
    }

    public function findBy(array $find_by = [], array $params = [])
    {
        $query = $this->sql_builder->select($this->entity_class, $find_by, $params);
        return $this->executor->select($this->entity_class, $query);
    }

    public function find(int $id)
    {
        $result = $this->findBy(["id = %1", $id]);

        if (is_array($result) && !empty($result[0])) {
            return $result[0];
        }

        return null;
    }

}