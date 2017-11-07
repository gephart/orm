<?php

namespace Gephart\ORM\Builder;

use Gephart\ORM\Connector;
use Gephart\ORM\Query\Condition;

/**
 * Condition builder
 *
 * @package Gephart\ORM\Builder
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
final class ConditionBuilder
{
    /**
     * @var Connector
     */
    private $connector;

    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    public function build(array $where_data = [], string $type = "AND"): Condition
    {
        $main = new Condition();
        $conditions = [];

        foreach ($where_data as $key => $value) {
            if ($key == "AND" && is_array($value)) {
                $conditions[] = $this->build($value);
            } elseif ($key == "OR" && is_array($value)) {
                $conditions[] = $this->build($value, "OR");
            } elseif (is_array($value)) {
                $tmp = $value[0];
                foreach ($value as $i => $and) {
                    $and = $this->connector->getPdo()->quote($and);
                    $tmp = str_replace("%$i", $and, $tmp);
                }
                $conditions[] = $tmp;
            } else {
                $tmp = $where_data[0];
                foreach ($where_data as $i => $and) {
                    $and = $this->connector->getPdo()->quote($and);
                    $tmp = str_replace("%$i", $and, $tmp);
                }
                $conditions[] = $tmp;
                break;
            }
        }

        foreach ($conditions as $condition) {
            switch ($type) {
                case "OR":
                    $main->addOr($condition);
                    break;
                default:
                    $main->addAnd($condition);
            }
        }

        return $main;
    }
}
