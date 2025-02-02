<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

/**
 * Test entity for mass assignment.
 */
class ProtectedArticle extends Article
{
    protected array $_accessible = [
        'title' => true,
        'body' => true,
    ];
}
