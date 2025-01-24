<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class UserProps extends Entity
{
    protected int $id;
    protected ?string $username;
    protected ?string $password;
    protected $created;
    protected $updated;
    protected $odd;

    protected $articles;
    protected $comments;
}
