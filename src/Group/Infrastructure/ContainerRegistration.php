<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Group\Infrastructure;

use Modestox\AdminStickyNotes\Infrastructure\Container;
use Modestox\AdminStickyNotes\Infrastructure\Container\ContainerRegistrationInterface;
use Modestox\AdminStickyNotes\Group\Admin\Action\{ListAction, FormAction, SaveAction, DeleteAction};
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Group\Service\GroupService;
use Modestox\AdminStickyNotes\Group\Admin\Controller as GroupController;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;

final class ContainerRegistration implements ContainerRegistrationInterface
{
    public static function register(Container $container): void
    {
        $container->set(GroupService::class, static fn(Container $c) => new GroupService(
            $c->get(GroupRepository::class),
        ));

        $container->set(GroupController::class, static fn(Container $c) => new GroupController(
            $c->get(ListAction::class),
            $c->get(FormAction::class),
            $c->get(SaveAction::class),
            $c->get(DeleteAction::class),
        ));

        $container->set(ListAction::class, static fn(Container $c) => new ListAction(
            $c->get(GroupRepository::class),
        ));

        $container->set(FormAction::class, static fn(Container $c) => new FormAction(
            $c->get(GroupRepository::class),
        ));

        $container->set(SaveAction::class, static fn(Container $c) => new SaveAction(
            $c->get(GroupRepository::class),
            $c->get(GroupService::class),
            $c->get(DateFactory::class),
        ));

        $container->set(DeleteAction::class, static fn(Container $c) => new DeleteAction(
            $c->get(GroupService::class),
        ));

        $container->set(GroupRepository::class, static fn(Container $c) => new GroupRepository(
            $c->get(DateFactory::class)
        ));
    }
}