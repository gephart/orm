<?php

namespace Gephart\ORM\Query;

/**
 * Join
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
abstract class Join implements RenderInterface
{
    use TableTrait;
    use ConditionTrait;

    /**
     * @var string
     */
    protected $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function render(): string
    {
        return "$this->type JOIN $this->table ON $this->condition";
    }
}
