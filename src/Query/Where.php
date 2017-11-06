<?php

namespace Gephart\ORM\Query;

/**
 * Where
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
final class Where implements RenderInterface
{
    use ConditionTrait;

    public function render(): string
    {
        if ($this->condition) {
            return "WHERE $this->condition";
        }

        return "";
    }
}
