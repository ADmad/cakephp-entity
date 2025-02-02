<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;
use BackedEnum;

class Comment extends Entity
{
    protected int $id;
    protected int $article_id;
    protected int $user_id;
    protected string $comment;
    protected string|BackedEnum $published;
    protected $created;
    protected $updated;
    protected $article;
    protected $user;
}
