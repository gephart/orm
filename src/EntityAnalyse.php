<?php

namespace Gephart\ORM;

/**
 * Entity analyse
 *
 * @package Gephart\ORM
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.2
 */
class EntityAnalyse
{
    /**
     * @var array
     */
    private $entity = [];

    /**
     * @var array
     */
    private $properties = [];

    /**
     * @return array
     */
    public function getEntity(): array
    {
        return $this->entity;
    }

    /**
     * @param array $entity
     */
    public function setEntity(array $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * @param string $property
     * @param array $property_data
     */
    public function addProperty(string $property, array $property_data)
    {
        $this->properties[$property] = $property_data;
    }
}