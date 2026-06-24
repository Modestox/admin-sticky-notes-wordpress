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
use Modestox\AdminStickyNotes\Notice\Ui\NoticeController;
use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;

/**
 * Explicit configuration registry dedicated exclusively for presentation layer controllers setup.
 */
final class ControllerDefinitions implements ContainerDefinitionInterface
{
    /**
     * @inheritDoc
     */
    public static function register(Container $container): void
    {
        $container->set(
            NoticeController::class,
            static fn(Container $c): NoticeController => new NoticeController(
                $c->get(NoticeRepository::class),
                $c->get(GroupRepository::class),
            ),
        );
    }
}