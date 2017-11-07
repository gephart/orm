<?php

namespace Gephart\ORM;

use Gephart\ORM\Builder\DeleteBuilder;
use Gephart\ORM\Builder\SelectBuilder;
use Gephart\ORM\Query\Condition;
use Gephart\ORM\Query\CreateTable;
use Gephart\Language\Language;
use Gephart\ORM\Query\Delete;
use Gephart\ORM\Query\LeftJoin;
use Gephart\ORM\Query\Limit;
use Gephart\ORM\Query\OrderBy;
use Gephart\ORM\Query\Select;
use Gephart\ORM\Query\Where;

/**
 * SQL builder
 *
 * @package Gephart\ORM
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.2
 */
class SQLBuilder
{
    /**
     * @var EntityAnalysator
     */
    private $entity_analysator;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var Language
     */
    private $language;

    /**
     * @var SelectBuilder
     */
    private $selectBuilder;

    /**
     * @var DeleteBuilder
     */
    private $deleteBuilder;

    /**
     * @param EntityAnalysator $entity_analysator
     * @param Connector $connector
     * @param Language $language
     */
    public function __construct(
        EntityAnalysator $entity_analysator,
        Connector $connector,
        Language $language,
        SelectBuilder $selectBuilder,
        DeleteBuilder $deleteBuilder
    ) {
        $this->entity_analysator = $entity_analysator;
        $this->language = $language;
        $this->pdo = $connector->getPdo();
        $this->selectBuilder = $selectBuilder;
        $this->deleteBuilder = $deleteBuilder;
    }

    /**
     * @param string $entity
     * @return string
     */
    public function createTable(string $entity): string
    {
        $entity_analyse = $this->entity_analysator->analyse($entity);
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

    /**
     * @param string $entity_name
     * @return string
     */
    public function syncTable(string $entity_name): string
    {
        $sqls = [];

        $entity_analyse = $this->entity_analysator->analyse($entity_name);
        $entity = $entity_analyse->getEntity();
        $properties = $entity_analyse->getProperties();

        $tables = $this->pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_ASSOC);

        $exists = false;
        foreach ($tables as $table) {
            if (current($table) == $entity["ORM\\Table"]) {
                $exists = true;
            }
        }

        if (!$exists) {
            return $this->createTable($entity_name);
        }


        $query = $this->pdo->query("SHOW COLUMNS FROM `".$entity["ORM\\Table"]."`");
        $columns = $query->fetchAll(\PDO::FETCH_ASSOC);
        $column_names = [];
        foreach ($columns as $column) {
            $column_names[] = $column["Field"];
        }

        foreach ($properties as $property) {
            if (!empty($property["ORM\\Type"])
                && !isset($property["ORM\\Translatable"])
                && !in_array($property["ORM\\Column"], $column_names)
            ) {
                $sqls[] = "ALTER TABLE `".$entity["ORM\\Table"]."` ADD `".$property["ORM\\Column"]
                    ."` ".$property["ORM\\Type"].";";
            } elseif (!empty($property["ORM\\Relation"])) {
                $sqls[] = "ALTER TABLE `".$entity["ORM\\Table"]."` ADD `".$property["ORM\\Column"]."` INT(6) UNSIGNED;";
            }
        }

        if (isset($entity["ORM\\Translation"])) {
            $query = $this->pdo->query("SHOW COLUMNS FROM `".$entity["ORM\\Table"]."_translation`");
            $columns = $query->fetchAll(\PDO::FETCH_ASSOC);
            $column_names = [];
            foreach ($columns as $column) {
                $column_names[] = $column["Field"];
            }

            foreach ($properties as $property) {
                if (!empty($property["ORM\\Type"])
                    && isset($property["ORM\\Translatable"])
                    && !in_array($property["ORM\\Column"], $column_names)
                ) {
                    $sqls[] = "ALTER TABLE `".$entity["ORM\\Table"]."_translation` ADD `".$property["ORM\\Column"]
                        ."` ".$property["ORM\\Type"].";";
                }
            }
        }

        $sql = implode(PHP_EOL, $sqls);

        return $sql;
    }

    /**
     * @param string $entity
     * @return string
     */
    public function deleteTable(string $entity): string
    {
        $entity_analyse = $this->entity_analysator->analyse($entity);
        $entity = $entity_analyse->getEntity();
        $sql = "DROP TABLE IF EXISTS `" . $entity["ORM\\Table"] . "_translation`;" . PHP_EOL;
        $sql .= "DROP TABLE IF EXISTS `" . $entity["ORM\\Table"] . "`;" . PHP_EOL;

        return $sql;
    }

    /**
     * @param $entity
     * @return string
     * @throws \Exception
     */
    public function delete($entity): string
    {
        $delete = $this->deleteBuilder->build($entity);
        return $delete->render();
    }

    /**
     * @param string $entity
     * @param array $where
     * @param array $params
     * @return string
     */
    public function select(string $entity, array $where = [], array $params = [])
    {
        $select = $this->selectBuilder->build($entity, $where, $params);
        return $select->render();
    }

    /**
     * @param $entity
     * @return array
     */
    public function update($entity): array
    {
        $entity_analyse = $this->entity_analysator->analyse(get_class($entity));
        $entity_data = $entity_analyse->getEntity();
        $properties = $entity_analyse->getProperties();

        $sqls = [];

        $table = "`" . $entity_data["ORM\\Table"] . "`";
        $columns = "";
        $values = "";
        $update = "";
        foreach ($properties as $property_name => $property) {
            $property_name = str_replace("_", "", $property_name);
            if (!isset($property["ORM\\Translatable"]) && !empty($property["ORM\\Column"])) {
                $column = "`".$property["ORM\\Column"]."`";
                if (method_exists($entity, "get".ucfirst($property_name))) {
                    $value = $entity->{"get" . ucfirst($property_name)}();
                } elseif (method_exists($entity, "is".ucfirst($property_name))) {
                    $value = (int) addslashes($entity->{"is" . ucfirst($property_name)}());
                }

                if ($value instanceof \DateTime) {
                    $value = $value->format("Y-m-d H:i:s");
                }

                $value = $this->pdo->quote($value);
                $columns .= $column.", ";
                $values .= $value.", ";
                $update .= "".$column." = ".$value.", ";
            } elseif (!isset($property["ORM\\Translatable"]) && isset($property["ORM\\Id"])) {
                $column = "`id`";
                $value = $entity->{"get" . ucfirst($property_name)}();
                $value = $this->pdo->quote($value);
                if ($value == "''") {
                    $value = "null";
                }
                $columns .= $column.", ";
                $values .= $value.", ";
                $update .= "".$column." = ".$value.", ";
            }
        }
        $values = trim($values, ", ");
        $columns = trim($columns, ", ");
        $update = trim($update, ", ");

        $sqls[] = "INSERT INTO $table ($columns) VALUES ($values) ON DUPLICATE KEY UPDATE $update;\n";

        if (isset($entity_data["ORM\\Translation"])) {
            $table = "`" . $entity_data["ORM\\Table"] . "_translation`";
            $columns = "";
            $values = "";
            $update = "";
            foreach ($properties as $property_name => $property) {
                $property_name = str_replace("_", "", $property_name);
                if (isset($property["ORM\\Translatable"]) && !empty($property["ORM\\Column"])) {
                    $column = "`" . $property["ORM\\Column"] . "`";

                    if (method_exists($entity, "get" . ucfirst($property_name))) {
                        $value = $entity->{"get" . ucfirst($property_name)}();
                    } elseif (method_exists($entity, "is" . ucfirst($property_name))) {
                        $value = (int)$entity->{"is" . ucfirst($property_name)}();
                    }

                    if ($value instanceof \DateTime) {
                        $value = $value->format("Y-m-d H:i:s");
                    }

                    $value = $this->pdo->quote($value);
                    $columns .= $column . ", ";
                    $values .= $value . ", ";
                    $update .= "" . $column . " = " . $value . ", ";
                } elseif (isset($property["ORM\\Id"])) {
                    $column = "`" . $entity_data["ORM\\Table"] . "_id`";

                    $value = $entity->{"get" . ucfirst($property_name)}();
                    $value = $this->pdo->quote($value);
                    if ($value == "''" || $value == "'0'") {
                        $value = "LAST_INSERT_ID()";
                    }
                    $columns .= $column . ", ";
                    $values .= $value . ", ";
                    $update .= "" . $column . " = " . $value . ", ";
                }
            }
            $values .= $this->pdo->quote($this->language->get()) . ",";
            $columns .= "`language`,";

            $values = trim($values, ", ");
            $columns = trim($columns, ", ");
            $update = trim($update, ", ");

            $sqls[] = "INSERT INTO $table ($columns) VALUES ($values) ON DUPLICATE KEY UPDATE $update;\n";
        }

        return $sqls;
    }

    /**
     * @param array $where_data
     * @param string $type
     * @return string
     */
    public function where(array $where_data = [], string $type = "AND"): string
    {
        $where = [];

        foreach ($where_data as $key => $value) {
            if ($key == "AND" && is_array($value)) {
                $where[] = trim($this->where($value), "()");
            } elseif ($key == "OR" && is_array($value)) {
                $where[] = $this->where($value, "OR");
            } elseif (is_array($value)) {
                $tmp = $value[0];
                foreach ($value as $i => $and) {
                    $tmp = str_replace("%$i", $and, $tmp);
                }
                $where[] = $tmp;
            } else {
                $tmp = $where_data[0];
                foreach ($where_data as $i => $and) {
                    $and = "'".addslashes($and)."'";
                    $tmp = str_replace("%$i", $and, $tmp);
                }
                $where[] = $tmp;
                break;
            }
        }

        if (count($where) > 0) {
            return "(" . implode(") $type (", $where) . ")";
        }

        return "1";
    }
}
