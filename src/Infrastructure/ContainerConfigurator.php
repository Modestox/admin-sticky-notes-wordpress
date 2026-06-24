<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Infrastructure;

use Modestox\AdminStickyNotes\Infrastructure\Container\RepositoryDefinitions;
use Modestox\AdminStickyNotes\Infrastructure\Container\ControllerDefinitions;
use Modestox\AdminStickyNotes\Infrastructure\Container\ServiceDefinitions;

/**
 * Orchestrator bootstrapper dispatching specific isolated definitions blocks configuration across the container.
 */
final class ContainerConfigurator
{
    /**
     * Binds domain resource assembly instructions by delegating routing maps to specific layer contexts.
     *
     * @param Container $container Central framework service container instance.
     * @return void
     */
    public static function configure(Container $container): void
    {
        RepositoryDefinitions::register($container);
        ControllerDefinitions::register($container);
        ServiceDefinitions::register($container);
    }
}