<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class ArticlesTag extends Entity
{
    protected int $article_id;
    protected int $tag_id;
    protected array $tags;
    protected $highlighted;
    protected $active;
    protected $tag;
    protected $article;
    protected $user;
    protected $foo;
}
