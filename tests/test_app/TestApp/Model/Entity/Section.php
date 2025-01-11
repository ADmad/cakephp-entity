<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class Section extends Entity
{
    protected int $id;
    protected string $title;
    protected array $members;
}
