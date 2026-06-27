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
use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Infrastructure\Wordpress\WpUserDirectory;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;

/**
 * Explicit configuration registry dedicated exclusively for Data Access Layer repositories.
 */
final class RepositoryDefinitions implements ContainerDefinitionInterface
{
    /**
     * @inheritDoc
     */
    public static function register(Container $container): void
    {
        // Shared Infrastructure Services
        $container->set(
            DateFactory::class,
            static fn(): DateFactory => new DateFactory()
        );

        // Repositories Data Layers
        $container->set(
            NoticeRepository::class,
            static fn(Container $c): NoticeRepository => new NoticeRepository(
                $c->get(DateFactory::class)
            )
        );

        $container->set(
            GroupRepository::class,
            static fn(Container $c): GroupRepository => new GroupRepository(
                $c->get(DateFactory::class)
            )
        );

        $container->set(
            WpUserDirectory::class,
            static fn(): WpUserDirectory => new WpUserDirectory()
        );
    }
}