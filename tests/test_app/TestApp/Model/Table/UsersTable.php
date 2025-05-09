<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;
use TestApp\Model\Entity\User;

/**
 * Used to test correct class is instantiated when using $this->getTableLocator()->get();
 */
class UsersTable extends Table
{
    protected ?string $_entityClass = User::class;
}
