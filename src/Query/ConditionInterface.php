<?php

namespace Gephart\ORM\Query;

/**
 * ConditionInterface
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
interface ConditionInterface
{
    public function __toString(): string;
}
