<?php

namespace Gephart\ORM\Query;

/**
 * SELECT query
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
final class Select implements RenderInterface
{
    use TableTrait;

    /**
     * @var Where
     * */
    private $where;

    /**
     * @var Condition
     * */
    private $join;

    /**
     * @var string
     */
    private $what;

    /**
     * @var OrderBy
     */
    private $orderBy;

    /**
     * @var Limit
     */
    private $limit;

    public function __construct(string $what)
    {
        $this->what = $what;
    }

    public function append(string $what)
    {
        $this->what .= $what;
    }

    /**
     * @param Condition $where
     */
    public function setWhere(Condition $where)
    {
        $this->where = $where;
    }

    /**
     * @param Join $join
     */
    public function setJoin(Join $join)
    {
        $this->join = $join;
    }

    /**
     * @param OrderBy $orderBy
     */
    public function setOrderBy(OrderBy $orderBy)
    {
        $this->orderBy = $orderBy;
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
        $sql = "SELECT $this->what FROM $this->table";

        if ($this->join) {
            $sql .= " " . $this->join->render();
        }

        if ($this->where) {
            $sql .= " WHERE " . $this->where->render();
        }

        if ($this->orderBy) {
            $sql .= " " . $this->orderBy->render();
        }

        if ($this->limit) {
            $sql .= " " . $this->limit->render();
        }

        return $sql;
    }
}
