<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class Member extends Entity
{
    protected int $id;
    protected int $section_count;
    protected $_joinData;
}
