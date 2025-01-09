<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class UserProps extends Entity
{
    protected $id;
    protected $name;
    protected $age;
    protected $phones;
}
