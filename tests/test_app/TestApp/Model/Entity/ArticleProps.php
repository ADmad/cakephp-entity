<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;
use BackedEnum;

class ArticleProps extends Entity
{
    protected ?int $id;
    protected ?int $author_id;
    protected string $title;
    protected string $body;
    protected string|BackedEnum $published;
    protected array $tags;
    protected AuthorProps $author;
    protected array $comments;
    protected array $unaproved_comments;
    protected $article;

    public function isRequired(): bool
    {
        return true;
    }
}
