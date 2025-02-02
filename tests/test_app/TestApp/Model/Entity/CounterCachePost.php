<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class CounterCachePost extends Entity
{
    protected int $id;
    protected string $title;
    protected ?int $user_id = null;
    protected ?int $category_id = null;
    protected bool $published;
    protected ?CounterCacheUser $user = null;
}
