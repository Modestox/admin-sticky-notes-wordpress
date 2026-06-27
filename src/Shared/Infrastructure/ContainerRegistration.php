<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Shared\Infrastructure;

use Modestox\AdminStickyNotes\Infrastructure\Container;
use Modestox\AdminStickyNotes\Infrastructure\Container\ContainerRegistrationInterface;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;
use Modestox\AdminStickyNotes\Infrastructure\Wordpress\WpUserDirectory;

final class ContainerRegistration implements ContainerRegistrationInterface
{
    public static function register(Container $container): void
    {
        $container->set(DateFactory::class, static fn() => new DateFactory());
        $container->set(WpUserDirectory::class, static fn() => new WpUserDirectory());
    }
}