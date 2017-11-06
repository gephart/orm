<?php

namespace Gephart\ORM\Query;

/**
 * RenderInterface
 *
 * @package Gephart\ORM\Query
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.6
 */
interface RenderInterface
{
    public function render(): string;
}
