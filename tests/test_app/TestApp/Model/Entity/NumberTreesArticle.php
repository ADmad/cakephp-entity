<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class NumberTreesArticle extends Entity
{
    protected int $id;
    protected ?int $number_tree_id = null;
    protected ?string $title = null;
    protected string $body;
    protected string $published = 'N';
}
