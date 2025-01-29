<?php
declare(strict_types=1);

namespace ADmad\Entity\Datasource;

class FallbackEntity extends Entity
{
    /**
     * @inheritDoc
     */
    public static bool $enableBackwardCompatibility = true;

    /**
     * @inheritDoc
     */
    protected bool $requireFieldPresence = false;
}
