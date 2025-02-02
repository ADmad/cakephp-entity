<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class AuthorsTranslation extends Entity
{
    protected string $locale;
    protected int $id;
    protected ?string $title;
    protected ?string $body;
}
