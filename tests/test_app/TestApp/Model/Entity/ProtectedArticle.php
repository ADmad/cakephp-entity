<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

/**
 * Test entity for mass assignment.
 */
class ProtectedArticle extends Article
{
    public function initialize(): void
    {
        $this->setAccess(['*'], false);
        $this->setAccess(['title', 'body'], true);
    }
}
