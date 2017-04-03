<?php

namespace Gephart\ORM;

class Executor
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var EntityAnalysator
     */
    private $entity_analysator;

    /**
     * @var SQLBuilder
     */
    private $sql_builder;

    public function __construct(Connector $connector, EntityAnalysator $entity_analysator, SQLBuilder $sql_builder)
    {
        $this->connector = $connector;
        $this->entity_analysator = $entity_analysator;
        $this->sql_builder = $sql_builder;
    }

    public function select(string $entity, string $query)
    {
        $entity_analyse = $this->entity_analysator->analyse($entity);
        $properties = $entity_analyse->getProperties();

        $pdo = $this->connector->getPdo();

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result_entities = [];
        foreach ($results as $result) {
            $result_entity = new $entity;

            foreach ($properties as $property_name=>$property) {
                if (!empty($property["ORM\\Column"])) {
                    if ($result[$property["ORM\\Column"]] !== null) {
                        $result_value = $result[$property["ORM\\Column"]];

                        if (isset($property["var"]) && $property["var"] == "\\DateTime") {
                            $result_value = new \DateTime($result_value);
                        }

                        $property_name = str_replace("_","",$property_name);
                        $result_entity->{"set".ucfirst($property_name)}($result_value);
                    }
                } elseif (isset($property["ORM\\Id"])) {
                    $result_entity->{"set".ucfirst($property_name)}($result["id"]);
                }
            }

            $result_entities[] = $result_entity;
        }

        return $result_entities;
    }
}