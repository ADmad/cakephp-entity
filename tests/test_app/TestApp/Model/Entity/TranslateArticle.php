<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ADmad\Entity\Datasource\Entity;
use Cake\ORM\Behavior\Translate\TranslateTrait;

class TranslateArticle extends Entity
{
    use TranslateTrait;

    protected $id;
    protected $title;
    protected $body;
    protected $description;
    protected $published;
    protected $locale;
    protected $author_id;
}
