<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class SiteArticle extends Entity
{
    protected int $id;
    protected ?int $author_id;
    protected int $site_id;
    protected string $title;
    protected string $body;
}
