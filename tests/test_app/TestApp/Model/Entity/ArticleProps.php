<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;
use BackedEnum;
use DateTimeInterface;

class ArticleProps extends Entity
{
    protected ?int $id;
    protected ?int $author_id;
    protected $user_id;
    protected string $title;
    protected ?string $body;
    protected string|BackedEnum $published;
    protected ?DateTimeInterface $created;
    protected ?DateTimeInterface $modified;
    protected ?DateTimeInterface $updated;
    protected ?DateTimeInterface $date_specialed;
    protected array $tags;
    protected ?AuthorProps $author;
    protected array $comments;
    protected array $unaproved_comments;
    protected $article;
    protected $not_in_schema;

    protected $comments_notype;
    protected $user;

    public function isRequired(): bool
    {
        return true;
    }
}
