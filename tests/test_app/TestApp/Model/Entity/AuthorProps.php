<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class AuthorProps extends Entity
{
    protected int $id;
    protected string $name;
    protected array $articles;
    protected int $total_articles;
    protected $supervisor;
    protected array $tags;
    protected array $site_articles;

    protected $article;
    protected $blogs;
}
