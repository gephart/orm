<?php

namespace Gephart\ORM\Query;

/**
 * Table
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
trait TableTrait
{
    /**
     * @var string $table
     */
    private $table;

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table)
    {
        $this->table = $table;
    }
}
