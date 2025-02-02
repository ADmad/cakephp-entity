<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @since         3.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace TestApp\Model\Table;

use Cake\ORM\Table;
use TestApp\Model\Entity\ArticlesTag;

/**
 * Tag table class
 */
class ArticlesTagsTable extends Table
{
    protected ?string $_entityClass = ArticlesTag::class;

    public function initialize(array $config): void
    {
        $this->belongsTo('Articles');
        $this->belongsTo('Tags');
    }
}
