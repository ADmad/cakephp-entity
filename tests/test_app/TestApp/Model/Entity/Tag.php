<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;
use DateTimeImmutable;

class Tag extends Entity
{
    protected int $id;
    protected string $tag;
    protected string $name;
    protected ?string $description;
    protected string|DateTimeImmutable|null $created;
    protected $articles;
}
