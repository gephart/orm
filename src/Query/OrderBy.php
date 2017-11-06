<?php

namespace Gephart\ORM\Query;

/**
 * OrderBy
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
final class OrderBy implements RenderInterface
{
    /**
     * @var string
     */
    private $column;

    /**
     * @var string
     */
    private $type = "";

    public function __construct(string $column)
    {

        $this->column = $column;
    }

    public function setDesc()
    {
        $this->type = " DESC";
    }

    public function setAsc()
    {
        $this->type = " ASC";
    }

    public function render(): string
    {
        return "ORDER BY $this->column $this->type";
    }
}
