<?php

namespace Gephart\ORM\Query;

/**
 * Left Join
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
final class LeftJoin extends Join
{
    public function __construct()
    {
        parent::__construct("LEFT");
    }
}
