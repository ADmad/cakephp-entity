<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;

class I18n extends Entity
{
    protected int $id;
    protected string $locale;
    protected string $model;
    protected int $foreign_key;
    protected string $field;
    protected ?string $content;
}
