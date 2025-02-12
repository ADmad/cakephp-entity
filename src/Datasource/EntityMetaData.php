<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) ADmad
 */
namespace ADmad\Entity\Datasource;

final class EntityMetaData
{
    /**
     * Holds field names for initialized properties
     *
     * @var array<string>
     */
    public array $propertyFields = [];

    /**
     * Holds all fields that have been changed and their original values for this entity.
     *
     * @var array<string, mixed>
     */
    public array $original = [];

    /**
     * Holds all fields that have been initially set on instantiation, or after marking as clean
     *
     * @var array<string>
     */
    public array $originalFields = [];

    /**
     * List of field names that should **not** be included in JSON or Array
     * representations of this Entity.
     *
     * @var array<string>
     */
    public array $hidden = [];

    /**
     * List of computed or virtual fields that **should** be included in JSON or array
     * representations of this Entity. If a field is present in both _hidden and _virtual
     * the field will **not** be in the array/JSON versions of the entity.
     *
     * @var array<string>
     */
    public array $virtual = [];

    /**
     * Holds a list of the fields that were modified or added after this object
     * was originally created.
     *
     * @var array<string, bool>
     */
    public array $dirty = [];

    /**
     * Indicates whether this entity is yet to be persisted.
     * Entities default to assuming they are new. You can use Table::persisted()
     * to set the new flag on an entity based on records in the database.
     *
     * @var bool
     */
    public bool $new = true;

    /**
     * List of errors per field as stored in this object.
     *
     * @var array<string, mixed>
     */
    public array $errors = [];

    /**
     * List of invalid fields and their data for errors upon validation/patching.
     *
     * @var array<string, mixed>
     */
    public array $invalid = [];

    /**
     * Map of fields in this entity that can be safely mass assigned, each
     * field name points to a boolean indicating its status. An empty array
     * means no fields are accessible for mass assigment.
     *
     * The special field '\*' can also be mapped, meaning that any other field
     * not defined in the map will take its value. For example, `'*' => true`
     * means that any field not defined in the map will be accessible for mass
     * assignment by default.
     *
     * @var array<string, bool>
     */
    public array $accessible = ['*' => true];

    /**
     * The alias of the repository this entity came from
     *
     * @var string
     */
    public string $registryAlias = '';

    /**
     * Storing the current visitation status while recursing through entities getting errors.
     *
     * @var bool
     */
    public bool $hasBeenVisited = false;

    /**
     * List of fields that can be dynamically set in this entity.
     *
     * @var array<string, true>
     */
    public array $allowedDynamicFields = [
        '_joinData' => true,
        '_matchingData' => true,
        '_locale' => true,
        '_translations' => true,
        '_i18n' => true,
    ];

    /**
     * Dynamically set field values.
     *
     * @var array<array-key, mixed>
     */
    public array $dynamicFields = [];
}
