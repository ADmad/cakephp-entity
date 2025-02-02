<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

/**
 * Entity for testing with hidden fields.
 */
class ProtectedUser extends User
{
    protected array $_hidden = ['password'];

    protected array $_accessible = [];
}
