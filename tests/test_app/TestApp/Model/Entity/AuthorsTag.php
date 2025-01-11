<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class AuthorsTag extends Entity
{
    protected int $author_id;
    protected int $tag_id;
    protected int $article_id;
}
