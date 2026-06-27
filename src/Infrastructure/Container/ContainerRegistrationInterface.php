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

interface ContainerRegistrationInterface
{
    public static function register(Container $container): void;
}