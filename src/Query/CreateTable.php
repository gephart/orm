<?php

namespace Gephart\ORM\Query;

/**
 * Create table query
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.2
 */
class CreateTable
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var array
     */
    private $columns;

    /**
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * @param string $column
     * @param string $type
     */
    public function addColumn(string $column, string $type)
    {
        $this->columns[$column] = "    `$column` $type";
    }

    /**
     * @return string
     */
    public function render()
    {
        $sql = "CREATE TABLE `" . $this->table . "` (" . PHP_EOL;
        $sql .= implode(",".PHP_EOL, $this->columns) . PHP_EOL;
        $sql .= ") CHARACTER SET utf8 COLLATE utf8_general_ci;" . PHP_EOL;
        return $sql;
    }
}
