<?php

namespace Gephart\ORM\Query;

/**
 * SELECT query
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
final class Delete implements RenderInterface
{
    use TableTrait;

    /**
     * @var Where
     * */
    private $where;

    /**
     * @var Limit
     */
    private $limit;

    /**
     * @param Condition $where
     */
    public function setWhere(Condition $where)
    {
        $this->where = $where;
    }

    /**
     * @param Limit $limit
     */
    public function setLimit(Limit $limit)
    {
        $this->limit = $limit;
    }

    public function render(): string
    {
        $sql = "DELETE FROM $this->table";

        if ($this->where) {
            $sql .= " WHERE " . $this->where->render();
        }

        if ($this->limit) {
            $sql .= " " . $this->limit->render();
        }

        return $sql;
    }
}
