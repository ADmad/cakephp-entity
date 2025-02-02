<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class CounterCacheUserCategoryPost extends Entity
{
    protected int $id;
    protected int $category_id;
    protected int $user_id;
    protected ?int $post_count = null;
}
