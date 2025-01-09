<?php
declare(strict_types=1);

namespace ADmad\Entity\Datasource;

class FallbackEntity extends Entity
{
    /**
     * Whether to allow dynamic properties to be set.
     *
     * This is used as the default value for all entities.
     *
     * @var bool
     */
    public static bool $enableBackwardCompatibility = true;
}
