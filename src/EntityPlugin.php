<?php
declare(strict_types=1);

namespace ADmad\Entity;

use Cake\Core\BasePlugin;

/**
 * Plugin for ADmad\Entity
 */
class EntityPlugin extends BasePlugin
{
    /**
     * Do bootstrapping or not
     *
     * @var bool
     */
    protected bool $bootstrapEnabled = false;

    /**
     * Console middleware
     *
     * @var bool
     */
    protected bool $consoleEnabled = false;

    /**
     * Enable middleware
     *
     * @var bool
     */
    protected bool $middlewareEnabled = false;

    /**
     * Register container services
     *
     * @var bool
     */
    protected bool $servicesEnabled = false;

    /**
     * Load routes or not
     *
     * @var bool
     */
    protected bool $routesEnabled = false;

    /**
     * Load events or not
     *
     * @var bool
     */
    protected bool $eventsEnabled = false;
}