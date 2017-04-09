<?php

namespace Gephart\ORM\Query;

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

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function addColumn(string $column, string $type)
    {
        $this->columns[$column] = "    `$column` $type";
    }

    public function render()
    {
        $sql = "CREATE TABLE `" . $this->table . "` (" . PHP_EOL;
        $sql .= implode(",".PHP_EOL, $this->columns) . PHP_EOL;
        $sql .= ") CHARACTER SET utf8 COLLATE utf8_general_ci;" . PHP_EOL;
        return $sql;
    }
}