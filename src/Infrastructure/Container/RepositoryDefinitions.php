<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Infrastructure\Container;

use Modestox\AdminStickyNotes\Infrastructure\Container;
use Modestox\AdminStickyNotes\Repository\Group\GroupRepository;
use Modestox\AdminStickyNotes\Repository\Notice\NoticeRepository;

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
        $container->set(
            NoticeRepository::class,
            static fn(): NoticeRepository => new NoticeRepository()
        );

        $container->set(GroupRepository::class, static function () {
            return new GroupRepository();
        });
    }
}