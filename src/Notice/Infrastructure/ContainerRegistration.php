<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Notice\Infrastructure;

use Modestox\AdminStickyNotes\Infrastructure\Container;
use Modestox\AdminStickyNotes\Infrastructure\Container\ContainerRegistrationInterface;
use Modestox\AdminStickyNotes\Notice\Admin\Action\{ListAction, FormAction, SaveAction, DeleteAction};
use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Notice\Service\NoticeService;
use Modestox\AdminStickyNotes\Notice\Admin\Controller as NoticeController;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Infrastructure\Wordpress\WpUserDirectory;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;
final class ContainerRegistration implements ContainerRegistrationInterface
{
    public static function register(Container $container): void
    {
        $container->set(NoticeService::class, static fn(Container $c) => new NoticeService(
            $c->get(NoticeRepository::class),
            $c->get(DateFactory::class),
        ));

        $container->set(NoticeController::class, static fn(Container $c) => new NoticeController(
            $c->get(ListAction::class),
            $c->get(FormAction::class),
            $c->get(SaveAction::class),
            $c->get(DeleteAction::class),
        ));

        $container->set(ListAction::class, static fn(Container $c) => new ListAction(
            $c->get(NoticeRepository::class),
            $c->get(GroupRepository::class),
            $c->get(WpUserDirectory::class),
        ));

        $container->set(FormAction::class, static fn(Container $c) => new FormAction(
            $c->get(NoticeRepository::class),
            $c->get(GroupRepository::class),
            $c->get(WpUserDirectory::class),
        ));

        $container->set(SaveAction::class, static fn(Container $c) => new SaveAction(
            $c->get(NoticeRepository::class),
            $c->get(NoticeService::class),
            $c->get(DateFactory::class),
        ));

        $container->set(DeleteAction::class, static fn(Container $c) => new DeleteAction(
            $c->get(NoticeService::class),
        ));

        $container->set(NoticeRepository::class, static fn(Container $c) => new NoticeRepository(
            $c->get(DateFactory::class)
        ));

    }
}