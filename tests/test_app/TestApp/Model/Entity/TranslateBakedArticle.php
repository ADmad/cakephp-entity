<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;
use Cake\ORM\Behavior\Translate\TranslateTrait;

class TranslateBakedArticle extends Entity
{
    use TranslateTrait;

    protected array $accessible = [
        'title' => true,
        'body' => true,
    ];

    protected $id;
    protected $title;
    protected $body;
    protected $locale;
}
