<?php

namespace Gephart\ORM\Builder;

use Gephart\ORM\EntityAnalysator;
use Gephart\ORM\Query\Condition;
use Gephart\ORM\Query\Delete;
use Gephart\ORM\Query\Limit;

/**
 * DELETE query builder
 *
 * @package Gephart\ORM\Builder
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
final class DeleteBuilder
{
    /**
     * @var EntityAnalysator
     */
    private $entityAnalysator;

    public function __construct(
        EntityAnalysator $entityAnalysator
    ) {
        $this->entityAnalysator = $entityAnalysator;
    }

    public function build($entity): Delete
    {
        $entity_analyse = $this->entityAnalysator->analyse(get_class($entity));
        $entity_data = $entity_analyse->getEntity();
        $properties = $entity_analyse->getProperties();

        if (empty($properties["id"]) || !isset($properties["id"]["ORM\\Id"])) {
            throw new \Exception("Entity '".get_class($entity)."' must have annotation ORM\\Id on id for remove.");
        }

        $delete = new Delete();
        $delete->setTable('`'. $entity_data["ORM\\Table"] . '`');
        $delete->setWhere(new Condition('`id` = '.(int)$entity->getId().''));
        $delete->setLimit(new Limit("1"));

        return $delete;
    }
}
