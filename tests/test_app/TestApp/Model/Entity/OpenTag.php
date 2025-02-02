<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

/**
 * Test entity for mass assignment.
 */
class OpenTag extends Tag
{
    protected array $_accessible = [
        'tag' => true,
    ];
}
