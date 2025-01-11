<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class PolymorphicTagged extends Entity
{
    protected int $id;
    protected int $tag_id;
    protected int $foreign_key;
    protected string $foreign_model;
    protected int $position;
    // protected $created;
    // protected $modified;
    protected $_joinData;
}
