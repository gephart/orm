<?php

namespace Gephart\ORM\Query;

/**
 * Left Join
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
final class Condition implements RenderInterface
{

    /** @var string[] */
    private $and = [];

    /** @var string[] */
    private $or = [];

    public function __construct($condition = "")
    {
        if (!empty($condition)) {
            $this->addAnd($condition);
        }
    }

    /**
     * @param string $and
     */
    public function addAnd($and)
    {
        if ($and instanceof Condition) {
            $and = $and->render();
        }

        if (!is_string($and)) {
            throw new \InvalidArgumentException(
                "Argument \$and must be string or instance of Gephart\ORM\Query\Condition"
            );
        }

        $this->and[] = $and;
    }

    /**
     * @param string $or
     */
    public function addOr($or)
    {
        if ($or instanceof Condition) {
            $or = $or->render();
        }

        if (!is_string($or)) {
            throw new \InvalidArgumentException(
                "Argument \$or must be string or instance of Gephart\ORM\Query\Condition"
            );
        }

        $this->or[] = $or;
    }

    public function render(): string
    {
        $ands = implode(" AND ", $this->and);
        $ors = implode(" OR ", $this->or);

        $ands = !empty($ands) ? "($ands)" : "";
        $ors = !empty($ors) ? "($ors)" : "";

        if (!empty($ands) && !empty($ors)) {
            return "$ands AND $ors";
        } elseif (!empty($ands)) {
            return $ands;
        } elseif (!empty($ors)) {
            return $ors;
        }

        return "";
        //throw new \RuntimeException("Blank condition");
    }
}
