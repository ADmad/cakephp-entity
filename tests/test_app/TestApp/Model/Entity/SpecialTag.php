<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class SpecialTag extends Entity
{
    protected $id;
    protected int $article_id;
    protected int $tag_id;
    protected $highlighted;
    protected $highlighted_time;
    protected $extra_info;
    protected $author_id;
}
