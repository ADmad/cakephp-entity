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

use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\MissingPropertyException;
use Cake\Datasource\InvalidPropertyInterface;
use InvalidArgumentException;
use PropertyHookType;
use ReflectionException;
use ReflectionProperty;

/**
 * An entity represents a single result row from a repository. It exposes the
 * methods for retrieving and storing properties associated in this row.
 *
 * This class is a modified version of Cake\ORM\Entity which by default uses
 * concrete properties instead of storing the field values internally in an array,
 * thus allowing the use of property hooks instead of method based mutators and accessors.
 * For the various features of entity to function it still relies on the use of
 * magic methods, so the properties *must* be declared as protected/private causing
 * `__get()`, `__set()` etc. to be triggered.
 *
 * Differences from Cake\ORM\Entity:
 *
 * - Method based mutators and accessors are not used, instead property hooks are used.
 * - When using the `get` property hook for virtual fields, they can be accessed
 *   only using the same casing, unlike ORM\Entity which allows accessing using
 *   either underscored or camel cased name.
 * - Indirect modification of properties is not allowed. For example, you cannot
 *   do `$entity->field['key'] = 'value'`, you must use
 *   `$entity->field = array_merge($entity->field, ['key' => 'value'])`.
 * - `has()` method will return false only for uninitialized properties, it will
 *   return `true` for properties set to `null`.
 * - Calling `unset()` for a hooked property will set it to `null` instead of
 *   unsetting it, since hooked properties can't be unset.
 */
class Entity implements EntityInterface, InvalidPropertyInterface
{
    private EntityMetaData $__data__;

    /**
     * Initializes the internal properties of this entity out of the
     * keys in an array. The following list of options can be used:
     *
     * - useSetters: whether use internal setters for properties or not (used only when using method based mutators)
     * - markClean: whether to mark all properties as clean after setting them
     * - markNew: whether this instance has not yet been persisted
     * - guard: whether to prevent inaccessible properties from being set (default: false)
     * - source: A string representing the alias of the repository this entity came from
     *
     * ### Example:
     *
     * ```
     *  $entity = new Entity(['id' => 1, 'name' => 'Andrew'])
     * ```
     *
     * @param array<string, mixed> $fields Hash of fields to set in this entity
     * @param array<string, mixed> $options list of options to use when creating this entity
     */
    public function __construct(array $fields = [], array $options = [])
    {
        $this->__data__ = new EntityMetaData();

        $this->initialize();

        $options += [
            'useSetters' => true,
            'markClean' => false,
            'markNew' => null,
            'guard' => false,
            'source' => null,
            'allowDynamic' => true,
        ];

        if ($options['source'] !== null) {
            $this->setSource($options['source']);
        }

        if ($options['markNew'] !== null) {
            $this->setNew($options['markNew']);
        }

        if ($fields) {
            $this->setOriginalField(array_keys($fields));

            $this->set($fields, [
                'setter' => $options['useSetters'],
                'guard' => $options['guard'],
                'allowDynamic' => $options['allowDynamic'],
            ]);
        }

        if ($options['markClean']) {
            $this->clean();
        }
    }

    /**
     * Intialize the entity.
     *
     * @return void
     */
    public function initialize(): void
    {
    }

    /**
     * Magic getter to access fields that have been set in this entity
     *
     * @param string $field Name of the field to access
     * @return mixed
     */
    public function &__get(string $field): mixed
    {
        return $this->get($field);
    }

    /**
     * Magic setter to add or edit a field in this entity
     *
     * @param string $field The name of the field to set
     * @param mixed $value The value to set to the field
     * @return void
     */
    public function __set(string $field, mixed $value): void
    {
        $this->set($field, $value);
    }

    /**
     * Returns whether this entity contains a field named $field
     * and is not set to null.
     *
     * @param string $field The field to check.
     * @return bool
     */
    public function __isset(string $field): bool
    {
        if (isset($this->__data__->allowedDynamicFields[$field])) {
            if (array_key_exists($field, $this->__data__->dynamicFields)) {
                return isset($this->__data__->dynamicFields[$field]);
            }
        }

        return isset($this->{$field});
    }

    /**
     * Removes a field from this entity
     *
     * @param string $field The field to unset
     * @return void
     */
    public function __unset(string $field): void
    {
        $this->unset($field);
    }

    /**
     * Sets a single field inside this entity.
     *
     * ### Example:
     *
     * ```
     * $entity->set('name', 'Andrew');
     * ```
     *
     * It is also possible to mass-assign multiple fields to this entity
     * with one call by passing a hashed array as fields in the form of
     * field => value pairs
     *
     * ### Example:
     *
     * ```
     * $entity->set(['name' => 'andrew', 'id' => 1]);
     * echo $entity->name // prints andrew
     * echo $entity->id // prints 1
     * ```
     *
     * Some times it is handy to bypass setter functions in this entity when assigning
     * fields. You can achieve this by disabling the `setter` option using the
     * `$options` parameter:
     *
     * ```
     * $entity->set('name', 'Andrew', ['setter' => false]);
     * $entity->set(['name' => 'Andrew', 'id' => 1], ['setter' => false]);
     * ```
     *
     * Mass assignment should be treated carefully when accepting user input, by default
     * entities will guard all fields when fields are assigned in bulk. You can disable
     * the guarding for a single set call with the `guard` option:
     *
     * ```
     * $entity->set(['name' => 'Andrew', 'id' => 1], ['guard' => false]);
     * ```
     *
     * You do not need to use the guard option when assigning fields individually:
     *
     * ```
     * // No need to use the guard option.
     * $entity->set('name', 'Andrew');
     * ```
     *
     * You can use the `asOriginal` option to set the given field as original, if it wasn't
     * present when the entity was instantiated.
     *
     * ```
     * $entity = new Entity(['name' => 'andrew', 'id' => 1]);
     *
     * $entity->set('phone_number', '555-0134');
     * print_r($entity->getOriginalFields()) // prints ['name', 'id']
     *
     * $entity->set('phone_number', '555-0134', ['asOriginal' => true]);
     * print_r($entity->getOriginalFields()) // prints ['name', 'id', 'phone_number']
     * ```
     *
     * @param array<string, mixed>|string $field the name of field to set or a list of
     * fields with their respective values
     * @param mixed $value The value to set to the field or an array if the
     * first argument is also an array, in which case will be treated as $options
     * @param array<string, mixed> $options Options to be used for setting the field. Allowed option
     * keys are `setter`, `guard` and `asOriginal`
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set(array|string $field, mixed $value = null, array $options = [])
    {
        if (is_string($field)) {
            $guard = false;
            $field = [$field => $value];
        } else {
            $guard = true;
            $options = (array)$value;
        }

        $options += [
            'setter' => true,
            'guard' => $guard,
            'asOriginal' => false,
            'allowDynamic' => static::class === self::class ? true : false,
        ];

        if ($options['asOriginal'] === true) {
            $this->setOriginalField(array_keys($field));
        }

        foreach ($field as $name => $value) {
            /** @psalm-suppress RedundantCastGivenDocblockType */
            $name = (string)$name;
            if ($name === '') {
                throw new InvalidArgumentException('Cannot set an empty field');
            }

            if ($options['guard'] === true && !$this->isAccessible($name)) {
                continue;
            }

            if (
                $this->isOriginalField($name) &&
                !array_key_exists($name, $this->__data__->original) &&
                in_array($name, $this->__data__->propertyFields, true) &&
                $value !== ($this->{$name} ?? null)
            ) {
                $this->__data__->original[$name] = $this->{$name} ?? null;
            }

            if (!in_array($name, $this->__data__->propertyFields, true)) {
                $this->__data__->propertyFields[] = $name;
            }

            $propExists = property_exists($this, $name);

            if (!$propExists && $options['allowDynamic']) {
                $this->__data__->allowedDynamicFields[$name] = true;
            }

            if ($this->isModified($name, $value)) {
                $this->setDirty($name, true);
            }

            if (!$propExists && isset($this->__data__->allowedDynamicFields[$name])) {
                $this->__data__->dynamicFields[$name] = $value;
                continue;
            }

            if ($options['setter']) {
                $this->{$name} = $value;

                continue;
            }

            $this->reflectedProperty($name)?->setRawValue($this, $value);
        }

        return $this;
    }

    /**
     * Check if the provided value is same as existing value for a field.
     *
     * @param string $field The field to check.
     * @return bool
     */
    protected function isModified(string $field, mixed $value): bool
    {
        if (
            isset($this->__data__->allowedDynamicFields[$field])
            && !property_exists($this, $field)
        ) {
            $existing = $this->__data__->dynamicFields[$field] ?? null;
        } else {
            $existing = $this->{$field} ?? null;
        }

        if (($value === null || is_scalar($value)) && $existing === $value) {
            return false;
        }

        if (
            is_object($value)
            && !($value instanceof EntityInterface)
            && $existing == $value
        ) {
            return false;
        }

        return true;
    }

    /**
     * Returns the value of a field by name
     *
     * @param string $field the name of the field to retrieve
     * @return mixed
     * @throws \InvalidArgumentException if an empty field name is passed
     */
    public function &get(string $field): mixed
    {
        if ($field === '') {
            throw new InvalidArgumentException('Cannot get an empty field');
        }

        $value = null;
        $fieldIsPresent = false;

        if (property_exists($this, $field)) {
            $fieldIsPresent = true;
            $value = $this->{$field} ?? null;
        } elseif (isset($this->__data__->allowedDynamicFields[$field])) {
            $fieldIsPresent = true;
            if (array_key_exists($field, $this->__data__->dynamicFields)) {
                $value = &$this->__data__->dynamicFields[$field];
            }
        }

        if (static::class === self::class) {
            return $value;
        }

        if (!$fieldIsPresent) {
            throw new MissingPropertyException([
                'property' => $field,
                'entity' => $this::class,
            ]);
        }

        return $value;
    }

    /**
     * Enable/disable field presence check when accessing a property.
     *
     * If enabled an exception will be thrown when trying to access a non-existent property.
     *
     * @param bool $value `true` to enable, `false` to disable.
     */
    public function requireFieldPresence(bool $value = true): void
    {
        throw new CakeException(
            'requireFieldPresence() is not supported in this class as use of actual properties is required.'
        );
    }

    /**
     * Returns whether a field has an original value
     *
     * @param string $field
     * @return bool
     */
    public function hasOriginal(string $field): bool
    {
        return array_key_exists($field, $this->__data__->original);
    }

    /**
     * Returns the value of an original field by name
     *
     * @param string $field the name of the field for which original value is retrieved.
     * @param bool $allowFallback whether to allow falling back to the current field value if no original exists
     * @return mixed
     * @throws \InvalidArgumentException if an empty field name is passed.
     */
    public function getOriginal(string $field, bool $allowFallback = true): mixed
    {
        if ($field === '') {
            throw new InvalidArgumentException('Cannot get an empty field');
        }
        if (array_key_exists($field, $this->__data__->original)) {
            return $this->__data__->original[$field];
        }

        if (!$allowFallback) {
            throw new InvalidArgumentException(sprintf('Cannot retrieve original value for field `%s`', $field));
        }

        return $this->get($field);
    }

    /**
     * Gets all original values of the entity.
     *
     * @return array
     */
    public function getOriginalValues(): array
    {
        $originals = $this->__data__->original;
        $originalKeys = array_keys($originals);
        foreach ($this->__data__->propertyFields as $key) {
            if (
                !in_array($key, $originalKeys, true) &&
                $this->isOriginalField($key)
            ) {
                $originals[$key] = $this->{$key};
            }
        }

        return $originals;
    }

    /**
     * Returns whether this entity contains a field named $field and is initialized.
     *
     * ### Example:
     *
     * ```
     * class MyEntity extends Entity
     * {
     *    protected $id;
     *    protected $name;
     *    protected $first_name;
     * }
     *
     * $entity = new MyEntity(['id' => 1, 'name' => null]);
     * $entity->has('id'); // true
     * $entity->has('name'); // true
     * $entity->has('first_name'); // false
     * $entity->has('last_name'); // false
     * ```
     *
     * You can check multiple fields by passing an array:
     *
     * ```
     * $entity->has(['name', 'last_name']);
     * ```
     *
     * @param array<string>|string $field The field or fields to check.
     * @return bool
     */
    public function has(array|string $field): bool
    {
        foreach ((array)$field as $prop) {
            $rp = $this->reflectedProperty($prop);
            if ($rp === null) {
                if (!array_key_exists($prop, $this->__data__->dynamicFields)) {
                    return false;
                }
            } elseif (!$rp->getHook(PropertyHookType::Get)) {
                if (!isset($this->{$prop})) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks that a field is empty
     *
     * This works similar to the `empty()` function expect that it will return
     * false for `0` and `'0'`.
     *
     * @param string $field The field to check.
     * @return bool
     */
    public function isEmpty(string $field): bool
    {
        $value = $this->has($field) ? $this->get($field) : null;
        if (
            $value === null ||
            $value === [] ||
            $value === ''
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks that a field has a value.
     *
     * This method will return true for
     *
     * - Non-empty strings
     * - Non-empty arrays
     * - Any object
     * - Integer, even `0`
     * - Float, even 0.0
     *
     * and false in all other cases.
     *
     * @param string $field The field to check.
     * @return bool
     */
    public function hasValue(string $field): bool
    {
        return !$this->isEmpty($field);
    }

    /**
     * Removes a field or list of fields from this entity
     *
     * ### Examples:
     *
     * ```
     * $entity->unset('name');
     * $entity->unset(['name', 'last_name']);
     * ```
     *
     * @param array<string>|string $field The field to unset.
     * @return $this
     */
    public function unset(array|string $field)
    {
        $field = (array)$field;
        foreach ($field as $p) {
            unset($this->__data__->dynamicFields[$p], $this->__data__->dirty[$p]);

            $pos = array_search($p, $this->__data__->propertyFields, true);
            if ($pos !== false) {
                unset($this->__data__->propertyFields[$pos]);
            }

            $rp = $this->reflectedProperty($p);
            if ($rp === null) {
                continue;
            }

            if ($rp->getHooks()) {
                $rp->setRawValue($this, null);

                continue;
            }

            unset($this->{$p});
        }

        return $this;
    }

    /**
     * Sets hidden fields.
     *
     * @param array<string> $fields An array of fields to hide from array exports.
     * @param bool $merge Merge the new fields with the existing. By default false.
     * @return $this
     */
    public function setHidden(array $fields, bool $merge = false)
    {
        if ($merge === false) {
            $this->__data__->hidden = $fields;

            return $this;
        }

        $fields = array_merge($this->__data__->hidden, $fields);
        $this->__data__->hidden = array_unique($fields);

        return $this;
    }

    /**
     * Gets the hidden fields.
     *
     * @return array<string>
     */
    public function getHidden(): array
    {
        return $this->__data__->hidden;
    }

    /**
     * Sets the virtual fields on this entity.
     *
     * @param array<string> $fields An array of fields to treat as virtual.
     * @param bool $merge Merge the new fields with the existing. By default false.
     * @return $this
     */
    public function setVirtual(array $fields, bool $merge = false)
    {
        if ($merge === false) {
            $this->__data__->virtual = $fields;

            return $this;
        }

        $fields = array_merge($this->__data__->virtual, $fields);
        $this->__data__->virtual = array_unique($fields);

        return $this;
    }

    /**
     * Gets the virtual fields on this entity.
     *
     * @return array<string>
     */
    public function getVirtual(): array
    {
        return $this->__data__->virtual;
    }

    /**
     * Gets the list of visible fields.
     *
     * The list of visible fields is all standard fields
     * plus virtual fields minus hidden fields.
     *
     * @return array<string> A list of fields that are 'visible' in all
     *     representations.
     */
    public function getVisible(): array
    {
        $fields = array_merge($this->__data__->propertyFields, $this->__data__->virtual);

        return array_diff($fields, $this->__data__->hidden);
    }

    /**
     * Returns an array with all the fields that have been set
     * to this entity
     *
     * This method will recursively transform entities assigned to fields
     * into arrays as well.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->getVisible() as $field) {
            $value = $this->get($field);
            if (is_array($value)) {
                $result[$field] = [];
                foreach ($value as $k => $entity) {
                    if ($entity instanceof EntityInterface) {
                        $result[$field][$k] = $entity->toArray();
                    } else {
                        $result[$field][$k] = $entity;
                    }
                }
            } elseif ($value instanceof EntityInterface) {
                $result[$field] = $value->toArray();
            } else {
                $result[$field] = $value;
            }
        }

        return $result;
    }

    /**
     * Returns the fields that will be serialized as JSON
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->extract($this->getVisible());
    }

    /**
     * Implements isset($entity);
     *
     * @param string $offset The offset to check.
     * @return bool Success
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * Implements $entity[$offset];
     *
     * @param string $offset The offset to get.
     * @return mixed
     */
    public function &offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Implements $entity[$offset] = $value;
     *
     * @param string $offset The offset to set.
     * @param mixed $value The value to set.
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Implements unset($result[$offset]);
     *
     * @param string $offset The offset to remove.
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->unset($offset);
    }

    /**
     * Returns an array with the requested fields
     * stored in this entity, indexed by field name
     *
     * @param array<string> $fields list of fields to be returned
     * @param bool $onlyDirty Return the requested field only if it is dirty
     * @return array<string, mixed>
     */
    public function extract(array $fields, bool $onlyDirty = false): array
    {
        $result = [];
        foreach ($fields as $field) {
            if (!$onlyDirty || $this->isDirty($field)) {
                $result[$field] = $this->get($field);
            }
        }

        return $result;
    }

    /**
     * Returns an array with the requested original fields
     * stored in this entity, indexed by field name, if they exist.
     *
     * Fields that are unchanged from their original value will be included in the
     * return of this method.
     *
     * @param array<string> $fields List of fields to be returned
     * @return array<string, mixed>
     */
    public function extractOriginal(array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            if ($this->hasOriginal($field)) {
                $result[$field] = $this->getOriginal($field);
            } elseif ($this->isOriginalField($field)) {
                $result[$field] = $this->get($field);
            }
        }

        return $result;
    }

    /**
     * Returns an array with only the original fields
     * stored in this entity, indexed by field name, if they exist.
     *
     * This method will only return fields that have been modified since
     * the entity was built. Unchanged fields will be omitted.
     *
     * @param array<string> $fields List of fields to be returned
     * @return array<string, mixed>
     */
    public function extractOriginalChanged(array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            if (!$this->hasOriginal($field)) {
                continue;
            }

            $original = $this->getOriginal($field);
            if ($original !== $this->get($field)) {
                $result[$field] = $original;
            }
        }

        return $result;
    }

    /**
     * Returns whether a field is an original one
     *
     * @return bool
     */
    public function isOriginalField(string $name): bool
    {
        return in_array($name, $this->__data__->originalFields);
    }

    /**
     * Returns an array of original fields.
     * Original fields are those that the entity was initialized with.
     *
     * @return array<string>
     */
    public function getOriginalFields(): array
    {
        return $this->__data__->originalFields;
    }

    /**
     * Sets the given field or a list of fields to as original.
     * Normally there is no need to call this method manually.
     *
     * @param array<string>|string $field the name of a field or a list of fields to set as original
     * @param bool $merge
     * @return $this
     */
    protected function setOriginalField(string|array $field, bool $merge = true)
    {
        if (!$merge) {
            $this->__data__->originalFields = (array)$field;

            return $this;
        }

        $fields = (array)$field;
        foreach ($fields as $field) {
            $field = (string)$field;
            if (!$this->isOriginalField($field)) {
                $this->__data__->originalFields[] = $field;
            }
        }

        return $this;
    }

    /**
     * Sets the dirty status of a single field.
     *
     * @param string $field the field to set or check status for
     * @param bool $isDirty true means the field was changed, false means
     * it was not changed. Defaults to true.
     * @return $this
     */
    public function setDirty(string $field, bool $isDirty = true)
    {
        if ($isDirty === false) {
            $this->setOriginalField($field);

            unset($this->__data__->dirty[$field], $this->__data__->original[$field]);

            return $this;
        }

        $this->__data__->dirty[$field] = true;
        unset($this->__data__->errors[$field], $this->__data__->invalid[$field]);

        return $this;
    }

    /**
     * Checks if the entity is dirty or if a single field of it is dirty.
     *
     * @param string|null $field The field to check the status for. Null for the whole entity.
     * @return bool Whether the field was changed or not
     */
    public function isDirty(?string $field = null): bool
    {
        return $field === null
            ? $this->__data__->dirty !== []
            : isset($this->__data__->dirty[$field]);
    }

    /**
     * Gets the dirty fields.
     *
     * @return array<string>
     */
    public function getDirty(): array
    {
        return array_keys($this->__data__->dirty);
    }

    /**
     * Sets the entire entity as clean, which means that it will appear as
     * no fields being modified or added at all. This is an useful call
     * for an initial object hydration
     *
     * @return void
     */
    public function clean(): void
    {
        $this->__data__->dirty = [];
        $this->__data__->errors = [];
        $this->__data__->invalid = [];
        $this->__data__->original = [];
        $this->setOriginalField($this->__data__->propertyFields, false);
    }

    /**
     * Set the status of this entity.
     *
     * Using `true` means that the entity has not been persisted in the database,
     * `false` that it already is.
     *
     * @param bool $new Indicate whether this entity has been persisted.
     * @return $this
     */
    public function setNew(bool $new)
    {
        if ($new) {
            foreach ($this->__data__->propertyFields as $k) {
                $this->__data__->dirty[$k] = true;
            }
        }

        $this->__data__->new = $new;

        return $this;
    }

    /**
     * Returns whether this entity has already been persisted.
     *
     * @return bool Whether the entity has been persisted.
     */
    public function isNew(): bool
    {
        return $this->__data__->new;
    }

    /**
     * Returns whether this entity has errors.
     *
     * @param bool $includeNested true will check nested entities for hasErrors()
     * @return bool
     */
    public function hasErrors(bool $includeNested = true): bool
    {
        if ($this->__data__->hasBeenVisited) {
            // While recursing through entities, each entity should only be visited once.
            // See https://github.com/cakephp/cakephp/issues/17318
            return false;
        }

        if (array_filter($this->__data__->errors)) {
            return true;
        }

        if ($includeNested === false) {
            return false;
        }

        $this->__data__->hasBeenVisited = true;
        try {
            foreach ($this->__data__->propertyFields as $field) {
                $value = $this->{$field};

                if ($this->_readHasErrors($value)) {
                    return true;
                }
            }
        } finally {
            $this->__data__->hasBeenVisited = false;
        }

        return false;
    }

    /**
     * Returns all validation errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        if ($this->__data__->hasBeenVisited) {
            // While recursing through entities, each entity should only be visited once. See https://github.com/cakephp/cakephp/issues/17318
            return [];
        }

        $diff = array_diff_key($this->__data__->propertyFields, array_keys($this->__data__->errors));
        $values = [];
        foreach ($diff as $field) {
            $values[$field] = $this->{$field};
        }

        $this->__data__->hasBeenVisited = true;
        try {
            $errors = $this->__data__->errors;
            foreach ($values as $field => $value) {
                if (is_array($value) || $value instanceof EntityInterface) {
                    $nestedErrors = $this->_readError($value);
                    if (!empty($nestedErrors)) {
                        $errors[$field] = $nestedErrors;
                    }
                }
            }
        } finally {
            $this->__data__->hasBeenVisited = false;
        }

        return $errors;
    }

    /**
     * Returns validation errors of a field
     *
     * @param string $field Field name to get the errors from
     * @return array
     */
    public function getError(string $field): array
    {
        return $this->__data__->errors[$field] ?? $this->_nestedErrors($field);
    }

    /**
     * Sets error messages to the entity
     *
     * ## Example
     *
     * ```
     * // Sets the error messages for multiple fields at once
     * $entity->setErrors(['salary' => ['message'], 'name' => ['another message']]);
     * ```
     *
     * @param array $errors The array of errors to set.
     * @param bool $overwrite Whether to overwrite pre-existing errors for $fields
     * @return $this
     */
    public function setErrors(array $errors, bool $overwrite = false)
    {
        if ($overwrite) {
            foreach ($errors as $f => $error) {
                $this->__data__->errors[$f] = (array)$error;
            }

            return $this;
        }

        foreach ($errors as $f => $error) {
            $this->__data__->errors += [$f => []];

            // String messages are appended to the list,
            // while more complex error structures need their
            // keys preserved for nested validator.
            if (is_string($error)) {
                $this->__data__->errors[$f][] = $error;
            } else {
                foreach ($error as $k => $v) {
                    $this->__data__->errors[$f][$k] = $v;
                }
            }
        }

        return $this;
    }

    /**
     * Sets errors for a single field
     *
     * ### Example
     *
     * ```
     * // Sets the error messages for a single field
     * $entity->setError('salary', ['must be numeric', 'must be a positive number']);
     * ```
     *
     * @param string $field The field to get errors for, or the array of errors to set.
     * @param array|string $errors The errors to be set for $field
     * @param bool $overwrite Whether to overwrite pre-existing errors for $field
     * @return $this
     */
    public function setError(string $field, array|string $errors, bool $overwrite = false)
    {
        if (is_string($errors)) {
            $errors = [$errors];
        }

        return $this->setErrors([$field => $errors], $overwrite);
    }

    /**
     * Auxiliary method for getting errors in nested entities
     *
     * @param string $field the field in this entity to check for errors
     * @return array Errors in nested entity if any
     */
    protected function _nestedErrors(string $field): array
    {
        // Only one path element, check for nested entity with error.
        if (!str_contains($field, '.')) {
            if (!$this->has($field)) {
                return [];
            }

            $entity = $this->get($field);
            if ($entity instanceof EntityInterface || is_iterable($entity)) {
                return $this->_readError($entity);
            }

            return [];
        }

        $path = explode('.', $field);

        // Try reading the errors data with field as a simple path
        $error = $this->getNestedVal($this->__data__->errors, $path);
        if ($error !== null) {
            return $error;
        }

        // Traverse down the related entities/arrays for
        // the relevant entity.
        $entity = $this;
        $len = count($path);
        while ($len) {
            /** @var string $part */
            $part = array_shift($path);
            $len = count($path);
            $val = null;
            if ($entity instanceof EntityInterface) {
                if ($entity->has($part)) {
                    $val = $entity->get($part);
                }
            } elseif (is_array($entity)) {
                $val = $entity[$part] ?? false;
            }

            if (
                is_iterable($val) ||
                $val instanceof EntityInterface
            ) {
                $entity = $val;
            } else {
                $path[] = $part;
                break;
            }
        }
        if (count($path) <= 1) {
            return $this->_readError($entity, array_pop($path));
        }

        return [];
    }

    /**
     * Get a nested value from an array.
     *
     * @param array $array The array to extract from.
     * @param array $path The path to traverse.
     * @return array|null
     */
    protected function getNestedVal(array $array, array $path): ?array
    {
        foreach ($path as $key) {
            if (isset($array[$key]) && is_array($array[$key])) {
                $array = $array[$key];
            } else {
                return null;
            }
        }

        return $array;
    }

    /**
     * Reads if there are errors for one or many values.
     *
     * @param mixed $value The object to read errors from.
     * @return bool
     */
    protected function _readHasErrors(mixed $value): bool
    {
        if ($value instanceof EntityInterface && $value->hasErrors()) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $v) {
                if ($this->_readHasErrors($v)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Read the error(s) from one or many objects.
     *
     * @param \Cake\Datasource\EntityInterface|iterable $object The object to read errors from.
     * @param string|null $path The field name for errors.
     * @return array
     */
    protected function _readError(EntityInterface|iterable $object, ?string $path = null): array
    {
        if ($path !== null && $object instanceof EntityInterface) {
            return $object->getError($path);
        }
        if ($object instanceof EntityInterface) {
            return $object->getErrors();
        }

        $array = array_map(function ($val) {
            if ($val instanceof EntityInterface) {
                return $val->getErrors();
            }
        }, (array)$object);

        return array_filter($array);
    }

    /**
     * Get a list of invalid fields and their data for errors upon validation/patching
     *
     * @return array<string, mixed>
     */
    public function getInvalid(): array
    {
        return $this->__data__->invalid;
    }

    /**
     * Get a single value of an invalid field. Returns null if not set.
     *
     * @param string $field The name of the field.
     * @return mixed|null
     */
    public function getInvalidField(string $field): mixed
    {
        return $this->__data__->invalid[$field] ?? null;
    }

    /**
     * Set fields as invalid and not patchable into the entity.
     *
     * This is useful for batch operations when one needs to get the original value for an error message after patching.
     * This value could not be patched into the entity and is simply copied into the _invalid property for debugging
     * purposes or to be able to log it away.
     *
     * @param array<string, mixed> $fields The values to set.
     * @param bool $overwrite Whether to overwrite pre-existing values for $field.
     * @return $this
     */
    public function setInvalid(array $fields, bool $overwrite = false)
    {
        foreach ($fields as $field => $value) {
            if ($overwrite) {
                $this->__data__->invalid[$field] = $value;
                continue;
            }
            $this->__data__->invalid += [$field => $value];
        }

        return $this;
    }

    /**
     * Sets a field as invalid and not patchable into the entity.
     *
     * @param string $field The value to set.
     * @param mixed $value The invalid value to be set for $field.
     * @return $this
     */
    public function setInvalidField(string $field, mixed $value)
    {
        $this->__data__->invalid[$field] = $value;

        return $this;
    }

    /**
     * Stores whether a field value can be changed or set in this entity.
     * The special field `*` can also be marked as accessible or protected, meaning
     * that any other field specified before will take its value. For example
     * `$entity->setAccess('*', true)` means that any field not specified already
     * will be accessible by default.
     *
     * You can also call this method with an array of fields, in which case they
     * will each take the accessibility value specified in the second argument.
     *
     * ### Example:
     *
     * ```
     * $entity->setAccess('id', true); // Mark id as not protected
     * $entity->setAccess('author_id', false); // Mark author_id as protected
     * $entity->setAccess(['id', 'user_id'], true); // Mark both fields as accessible
     * $entity->setAccess('*', false); // Mark all fields as protected
     * ```
     *
     * @param array<string>|string $field Single or list of fields to change its accessibility
     * @param bool $set True marks the field as accessible, false will
     * mark it as protected.
     * @return $this
     */
    public function setAccess(array|string $field, bool $set)
    {
        if ($field === '*') {
            $this->__data__->accessible = array_map(fn ($p) => $set, $this->__data__->accessible);
            $this->__data__->accessible['*'] = $set;

            return $this;
        }

        foreach ((array)$field as $prop) {
            $this->__data__->accessible[$prop] = $set;
        }

        return $this;
    }

    /**
     * Returns the raw accessible configuration for this entity.
     * The `*` wildcard refers to all fields.
     *
     * @return array<bool>
     */
    public function getAccessible(): array
    {
        return $this->__data__->accessible;
    }

    /**
     * Checks if a field is accessible
     *
     * ### Example:
     *
     * ```
     * $entity->isAccessible('id'); // Returns whether it can be set or not
     * ```
     *
     * @param string $field Field name to check
     * @return bool
     */
    public function isAccessible(string $field): bool
    {
        $value = $this->__data__->accessible[$field] ?? null;

        return ($value === null && !empty($this->__data__->accessible['*'])) || $value;
    }

    /**
     * Returns the alias of the repository from which this entity came from.
     *
     * @return string
     */
    public function getSource(): string
    {
        return $this->__data__->registryAlias;
    }

    /**
     * Sets the source alias
     *
     * @param string $alias the alias of the repository
     * @return $this
     */
    public function setSource(string $alias)
    {
        $this->__data__->registryAlias = $alias;

        return $this;
    }

    /**
     * Get ReflectedProperty instance for a property.
     *
     * @param string $name Property name
     * @return \ReflectionProperty|null
     */
    protected function reflectedProperty(string $name): ?ReflectionProperty
    {
        try {
            return new ReflectionProperty($this, $name);
        } catch (ReflectionException) {
            return null;
        }
    }

    /**
     * Returns a string representation of this object in a human readable format.
     *
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }

    /**
     * Clone the entity along with its data.
     *
     * @return void
     */
    public function __clone(): void
    {
        $this->__data__ = clone $this->__data__;
    }

    /**
     * Returns an array that can be used to describe the internal state of this
     * object.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        $fields = [];
        foreach ($this->__data__->propertyFields as $field) {
            $fields[$field] = $this->{$field};
        }
        $fields += $this->__data__->dynamicFields;

        foreach ($this->__data__->virtual as $field) {
            $fields[$field] = $this->{$field};
        }

        return $fields + [
            '[new]' => $this->isNew(),
            '[accessible]' => $this->__data__->accessible,
            '[dirty]' => $this->__data__->dirty,
            '[allowedDynamic]' => array_keys($this->__data__->allowedDynamicFields),
            '[original]' => $this->__data__->original,
            '[originalFields]' => $this->__data__->originalFields,
            '[virtual]' => $this->__data__->virtual,
            '[hasErrors]' => $this->hasErrors(),
            '[errors]' => $this->__data__->errors,
            '[invalid]' => $this->__data__->invalid,
            '[repository]' => $this->__data__->registryAlias,
        ];
    }
}
