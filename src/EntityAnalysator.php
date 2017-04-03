<?php

namespace Gephart\ORM;

use Gephart\Annotation\Reader;

class EntityAnalysator
{
    private $entity_must_have_annotation = [
        "ORM\\Table"
    ];

    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function analyse(string $entity): EntityAnalyse
    {
        if (!class_exists($entity)) {
            throw new \Exception("Class '$entity' not exists");
        }

        $entity_analyse = new EntityAnalyse();

        $entity_annotation = $this->reader->getAll($entity);
        $this->checkValidEntity($entity, $entity_annotation);
        $entity_analyse->setEntity($entity_annotation);

        $entity_reflection = new \ReflectionClass($entity);
        $properties_reflection = $entity_reflection->getProperties();
        foreach ($properties_reflection as $property_reflection) {
            $property = $property_reflection->getName();
            $property_data = $this->reader->getAllProperty($entity, $property);
            $entity_analyse->addProperty($property, $property_data);
        }

        return $entity_analyse;
    }

    private function checkValidEntity(string $entity, array $entity_annotation)
    {
        foreach ($this->entity_must_have_annotation as $must_have) {
            if (!in_array($must_have, array_keys($entity_annotation))) {
                throw new \Exception("Entity '$entity' must have annotation: '$must_have'");
            }
        }
    }

}