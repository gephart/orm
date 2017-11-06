<?php

namespace Gephart\ORM\Query;

/**
 * Limit
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
final class Limit implements RenderInterface
{

    /**
     * @var string
     */
    private $limit;

    public function __construct(string $limit)
    {
        $this->limit = $limit;
    }

    public function render(): string
    {
        return "LIMIT $this->limit";
    }
}
