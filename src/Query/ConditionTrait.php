<?php

namespace Gephart\ORM\Query;

/**
 * Condition
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
trait ConditionTrait
{
    /** @var string $condition */
    private $condition;

    /**
     * @return string
     */
    public function getCondition(): string
    {
        return $this->condition;
    }

    /**
     * @param Condition $condition
     */
    public function setCondition(Condition $condition)
    {
        $this->condition = $condition->render();
    }
}
