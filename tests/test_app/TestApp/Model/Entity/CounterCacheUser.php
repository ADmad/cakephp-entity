<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class CounterCacheUser extends Entity
{
    protected int $id;
    protected string $name;
    protected ?int $post_count;
    protected ?int $comment_count;
    protected ?int $posts_published;
}
