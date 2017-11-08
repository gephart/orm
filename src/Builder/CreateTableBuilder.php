<?php

namespace Gephart\ORM\Builder;

use Gephart\ORM\EntityAnalysator;
use Gephart\ORM\Query\CreateTable;

/**
 * CREATE TABLE query builder
 *
 * @package Gephart\ORM\Builder
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
final class CreateTableBuilder
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

    public function build($entity): string
    {
        $entity_analyse = $this->entityAnalysator->analyse($entity);
        $entity = $entity_analyse->getEntity();
        $properties = $entity_analyse->getProperties();
        $query = new CreateTable($entity["ORM\\Table"]);
        $sqls = [];
        foreach ($properties as $property) {
            if (isset($property["ORM\\Id"])) {
                $query->addColumn("id", "INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY");
            } elseif (!empty($property["ORM\\Type"]) && !isset($property["ORM\\Translatable"])) {
                $query->addColumn($property["ORM\\Column"], $property["ORM\\Type"]);
            } elseif (!empty($property["ORM\\Relation"])) {
                $query->addColumn($property["ORM\\Column"], "INT(6) UNSIGNED");
            }
        }
        $sqls[] = $query->render();


        if (isset($entity["ORM\\Translation"])) {
            $query = new CreateTable($entity["ORM\\Table"] . "_translation");
            $query->addColumn($entity["ORM\\Table"] . "_id", "INT(6) UNSIGNED");
            $query->addColumn("language", "VARCHAR(5)");
            foreach ($properties as $property) {
                if (!empty($property["ORM\\Type"]) && isset($property["ORM\\Translatable"])) {
                    $query->addColumn($property["ORM\\Column"], $property["ORM\\Type"]);
                }
            }
            $sqls[] = $query->render();

            $sqls[] = "ALTER TABLE `" . $entity["ORM\\Table"] . "_translation`
              ADD UNIQUE `" . $entity["ORM\\Table"] . "_id_language` (`" . $entity["ORM\\Table"] . "_id`, `language`);"
                . PHP_EOL;
            $sqls[] = "ALTER TABLE `" . $entity["ORM\\Table"] . "_translation`
              ADD FOREIGN KEY (`" . $entity["ORM\\Table"] . "_id`) REFERENCES `" . $entity["ORM\\Table"]
                . "` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;" . PHP_EOL;
        }
        $sql = implode(PHP_EOL, $sqls);

        return $sql;
    }
}
