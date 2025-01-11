<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class Category extends Entity
{
    protected int $id;
    protected int $parent_id;
    protected string $name;
    protected $created;
    protected $updated;
    protected Category $parent;
    protected array $children;
}
