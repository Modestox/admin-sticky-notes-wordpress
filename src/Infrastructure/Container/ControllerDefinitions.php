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
use Modestox\AdminStickyNotes\Notice\Admin\Controller as NoticeController;
use Modestox\AdminStickyNotes\Notice\Admin\Action\ListAction;
use Modestox\AdminStickyNotes\Notice\Admin\Action\FormAction;
use Modestox\AdminStickyNotes\Notice\Admin\Action\SaveAction;
use Modestox\AdminStickyNotes\Notice\Admin\Action\DeleteAction;
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
        // 1. Регистрируем Single Actions в контейнере фабрик
        $container->set(
            ListAction::class,
            static fn(Container $c): ListAction => new ListAction($c->get(NoticeRepository::class), $c->get(GroupRepository::class)),
        );

        $container->set(
            FormAction::class,
            static fn(Container $c): FormAction => new FormAction($c->get(NoticeRepository::class), $c->get(GroupRepository::class)),
        );

        $container->set(
            SaveAction::class,
            static fn(Container $c): SaveAction => new SaveAction($c->get(NoticeRepository::class)),
        );

        $container->set(
            DeleteAction::class,
            static fn(Container $c): DeleteAction => new DeleteAction($c->get(NoticeRepository::class)),
        );

        $container->set(
            NoticeController::class,
            static fn(Container $c): NoticeController => new NoticeController(
                $c->get(ListAction::class),
                $c->get(FormAction::class),
                $c->get(SaveAction::class),
                $c->get(DeleteAction::class),
            ),
        );
    }
}