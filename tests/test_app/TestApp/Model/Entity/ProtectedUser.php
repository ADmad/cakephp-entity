<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

/**
 * Entity for testing with hidden fields.
 */
class ProtectedUser extends User
{
    public function initialize(): void
    {
        $this->setAccess(['*'], false);
        $this->setHidden(['password']);
    }
}
