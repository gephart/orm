<?php

namespace Gephart\ORM;

use Gephart\Language\Language;

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

    public function __construct(EntityAnalysator $entity_analysator, Connector $connector, Language $language)
    {
        $this->entity_analysator = $entity_analysator;
        $this->language = $language;
        $this->pdo = $connector->getPdo();
    }

    public function createTable(string $entity): string
    {
        $entity_analyse = $this->entity_analysator->analyse($entity);
        $entity = $entity_analyse->getEntity();
        $properties = $entity_analyse->getProperties();
        $sql = "CREATE TABLE `" . $entity["ORM\\Table"] . "` (" . PHP_EOL;
        foreach ($properties as $property) {
            if (isset($property["ORM\\Id"])) {
                $sql .= "  `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY," . PHP_EOL;
            } elseif (!empty($property["ORM\\Type"]) && !isset($property["ORM\\Translatable"])) {
                $sql .= "  `" . $property["ORM\\Column"] . "` " . $property["ORM\\Type"] . "," . PHP_EOL;
            } elseif (!empty($property["ORM\\Relation"])) {
                $sql .= "  `" . $property["ORM\\Column"] . "` INT(6) UNSIGNED," . PHP_EOL;
            }
        }
        $sql = trim($sql, PHP_EOL . ",") . PHP_EOL;
        $sql .= ") CHARACTER SET utf8 COLLATE utf8_general_ci;" . PHP_EOL;

        if (isset($entity["ORM\\Translation"])) {
            $sql .= PHP_EOL . "CREATE TABLE `" . $entity["ORM\\Table"] . "_translation` (" . PHP_EOL;
            $sql .= "  `" . $entity["ORM\\Table"] . "_id` INT(6) UNSIGNED," . PHP_EOL;
            $sql .= "  `language` VARCHAR(5)," . PHP_EOL;
            foreach ($properties as $property) {
                if (!empty($property["ORM\\Type"]) && isset($property["ORM\\Translatable"])) {
                    $sql .= "  `" . $property["ORM\\Column"] . "` " . $property["ORM\\Type"] . "," . PHP_EOL;
                }
            }
            $sql = trim($sql, PHP_EOL . ",") . PHP_EOL;
            $sql .= ") CHARACTER SET utf8 COLLATE utf8_general_ci;" . PHP_EOL;
            $sql .= "ALTER TABLE `" . $entity["ORM\\Table"] . "_translation`
              ADD UNIQUE `" . $entity["ORM\\Table"] . "_id_language` (`" . $entity["ORM\\Table"] . "_id`, `language`);";
            $sql .= "ALTER TABLE `" . $entity["ORM\\Table"] . "_translation`
              ADD FOREIGN KEY (`" . $entity["ORM\\Table"] . "_id`) REFERENCES `" . $entity["ORM\\Table"] . "` (`id`) ON DELETE CASCADE ON UPDATE CASCADE";
        }

        return $sql;
    }

    public function deleteTable(string $entity): string
    {
        $entity_analyse = $this->entity_analysator->analyse($entity);
        $entity = $entity_analyse->getEntity();
        $sql = "DROP TABLE IF EXISTS `" . $entity["ORM\\Table"] . "_translation`;" . PHP_EOL;
        $sql .= "DROP TABLE IF EXISTS `" . $entity["ORM\\Table"] . "`;" . PHP_EOL;

        return $sql;
    }

    public function delete($entity): string
    {
        $entity_analyse = $this->entity_analysator->analyse(get_class($entity));
        $entity_data = $entity_analyse->getEntity();
        $properties = $entity_analyse->getProperties();

        if (empty($properties["id"]) || !isset($properties["id"]["ORM\\Id"])) {
            throw new \Exception("Entity '".get_class($entity)."' must have annotation ORM\\Id on id for remove.");
        }

        $sql = 'DELETE FROM `'. $entity_data["ORM\\Table"] . '` WHERE `id` = '.(int)$entity->getId().' LIMIT 1';

        return $sql;
    }

    public function select(string $entity, array $where = [], array $params = [])
    {
        $entity_analyse = $this->entity_analysator->analyse($entity);
        $entity = $entity_analyse->getEntity();
        $properties = $entity_analyse->getProperties();

        $tables_count = 1;

        $select = "t_$tables_count.*";
        $from = "`" . $entity["ORM\\Table"] . "` t_$tables_count";

        $where = $this->where($where);
        if ($where) {
            $where = "WHERE " . $where;
        }

        if (isset($entity["ORM\\Translation"])) {
            $tables_count++;
            foreach ($properties as $property) {
                if (isset($property["ORM\\Translatable"])) {
                    $select .= ", t_$tables_count.`".$property["ORM\\Column"]."`";
                }
            }
            $from .= " LEFT JOIN `" . $entity["ORM\\Table"] . "_translation` t_$tables_count ON t_$tables_count.`" . $entity["ORM\\Table"] . "_id` = t_".($tables_count-1).".`id`";
            $where .= " AND t_$tables_count.`language` = " . $this->pdo->quote($this->language->get());
        }
        
        // TODO - add relations

        $sql = "SELECT $select FROM $from $where";
        if (!empty($params["LIMIT"])) {
            $sql .= " LIMIT " . $params["LIMIT"];
        }
        if (!empty($params["ORDER BY"])) {
            $sql .= " ORDER BY " . $params["ORDER BY"];
        }

        return $sql;
    }

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
        foreach ($properties as $property_name=>$property) {
            $property_name = str_replace("_","",$property_name);
            if (!isset($property["ORM\\Translatable"]) && !empty($property["ORM\\Column"])) {
                $column = "`".$property["ORM\\Column"]."`";
                if (method_exists($entity,"get".ucfirst($property_name))) {
                    $value = $entity->{"get" . ucfirst($property_name)}();
                } elseif (method_exists($entity,"is".ucfirst($property_name))) {
                    $value = (int) addslashes($entity->{"is" . ucfirst($property_name)}());
                }
                $value = $this->pdo->quote($value);
                $columns .= $column.", ";
                $values .= $value.", ";
                $update .= "".$column." = ".$value.", ";
            } elseif(!isset($property["ORM\\Translatable"]) && isset($property["ORM\\Id"])){
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

    public function where(array $where_data = [], string $type = "AND"): string
    {
        $where = [];

        foreach ($where_data as $key => $value) {
            if ($key == "AND" && is_array($value)) {
                $where[] = trim($this->where($value),"()");
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