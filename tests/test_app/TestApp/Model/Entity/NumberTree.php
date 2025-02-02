<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;
use Cake\ORM\Behavior\Translate\TranslateTrait;

/**
 * Stub entity class
 */
class NumberTree extends Entity
{
    use TranslateTrait;

    protected int $id;
    protected string $name;
    protected ?int $parent_id = null;
    protected int $lft;
    protected int $rght;
    protected ?int $depth;
    protected array $children;
}
