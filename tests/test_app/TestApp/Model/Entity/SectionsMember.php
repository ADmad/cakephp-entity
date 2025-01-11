<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class SectionsMember extends Entity
{
    protected int $id;
    protected int $section_id;
    protected int $member_id;
}
