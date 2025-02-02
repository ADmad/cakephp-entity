<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class MenuLinkTree extends Entity
{
    protected int $id;
    protected string $menu;
    protected int $lft;
    protected int $rght;
    protected ?int $parent_id = null;
    protected string $url;
    protected string $title;
    protected array $children;
}
