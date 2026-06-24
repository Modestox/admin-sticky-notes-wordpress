<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Infrastructure\Container;

use Modestox\AdminStickyNotes\Infrastructure\Container;

/**
 * Standard structural contract for highly isolated layer component dependency binders.
 */
interface ContainerDefinitionInterface
{
    /**
     * Registers specific layer isolated dependency compounds inside the environment container wrapper.
     *
     * @param Container $container Central framework service container instance.
     * @return void
     */
    public static function register(Container $container): void;
}