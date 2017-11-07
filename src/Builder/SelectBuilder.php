<?php

namespace Gephart\ORM\Builder;

use Gephart\Language\Language;
use Gephart\ORM\Connector;
use Gephart\ORM\EntityAnalysator;
use Gephart\ORM\Query\Condition;
use Gephart\ORM\Query\LeftJoin;
use Gephart\ORM\Query\Limit;
use Gephart\ORM\Query\OrderBy;
use Gephart\ORM\Query\Select;

/**
 * SELECT query builder
 *
 * @package Gephart\ORM\Builder
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
final class SelectBuilder
{
    /**
     * @var int
     */
    private $tableCount = 0;

    /**
     * @var EntityAnalysator
     */
    private $entityAnalysator;

    /**
     * @var ConditionBuilder
     */
    private $conditionBuilder;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var Language
     */
    private $language;

    public function __construct(
        EntityAnalysator $entityAnalysator,
        Connector $connector,
        ConditionBuilder $conditionBuilder,
        Language $language
    ) {
        $this->entityAnalysator = $entityAnalysator;
        $this->conditionBuilder = $conditionBuilder;
        $this->connector = $connector;
        $this->language = $language;
    }

    public function build(string $entity, array $where = [], array $params = []): Select
    {
        $entity_analyse = $this->entityAnalysator->analyse($entity);
        $entity = $entity_analyse->getEntity();
        $properties = $entity_analyse->getProperties();

        $tables_count = 1;

        $select = new Select("t_$tables_count.*");
        $select->setTable("`" . $entity["ORM\\Table"] . "` t_$tables_count");

        $condition = $this->conditionBuilder->build($where);

        if (isset($entity["ORM\\Translation"])) {
            $tables_count++;

            $join = new LeftJoin();
            $join->setTable("`" . $entity["ORM\\Table"] . "_translation` t_$tables_count");

            $join->setCondition(
                new Condition(
                    "t_$tables_count.`" . $entity["ORM\\Table"] . "_id` = t_".($tables_count-1).".`id`"
                )
            );

            foreach ($properties as $property) {
                if (isset($property["ORM\\Translatable"])) {
                    $select->append(", t_$tables_count.`".$property["ORM\\Column"]."`");
                }
            }

            $condition->addAnd(
                "t_$tables_count.`language` = "
                . $this->connector->getPdo()->quote($this->language->get())
            );

            $select->setJoin($join);
        }

        // TODO - add relations

        $select->setWhere($condition);

        if (!empty($params["ORDER BY"])) {
            $select->setOrderBy(new OrderBy($params["ORDER BY"]));
        }
        if (!empty($params["LIMIT"])) {
            $select->setLimit(new Limit($params["LIMIT"]));
        }

        return $select;
    }
}
