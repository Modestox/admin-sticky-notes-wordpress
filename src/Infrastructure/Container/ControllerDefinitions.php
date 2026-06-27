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
use Modestox\AdminStickyNotes\Notice\Admin\Action\ListAction as NoticeListAction;
use Modestox\AdminStickyNotes\Notice\Admin\Action\FormAction as NoticeFormAction;
use Modestox\AdminStickyNotes\Notice\Admin\Action\SaveAction as NoticeSaveAction;
use Modestox\AdminStickyNotes\Notice\Admin\Action\DeleteAction as NoticeDeleteAction;
use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Notice\Service\NoticeService;
use Modestox\AdminStickyNotes\Notice\Admin\Controller as NoticeController;

use Modestox\AdminStickyNotes\Group\Admin\Action\ListAction as GroupListAction;
use Modestox\AdminStickyNotes\Group\Admin\Action\FormAction as GroupFormAction;
use Modestox\AdminStickyNotes\Group\Admin\Action\SaveAction as GroupSaveAction;
use Modestox\AdminStickyNotes\Group\Admin\Action\DeleteAction as GroupDeleteAction;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Group\Service\GroupService;
use Modestox\AdminStickyNotes\Group\Admin\Controller as GroupController;

use Modestox\AdminStickyNotes\Infrastructure\Wordpress\WpUserDirectory;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;

/**
 * Service provider binding routing controllers and administrative action workflows into the DI Container.
 */
final class ControllerDefinitions implements ContainerDefinitionInterface
{
    /**
     * @inheritDoc
     */
    public static function register(Container $container): void
    {
        // ====================================================================
        // NOTICE MODULE COMPOUNDS
        // ====================================================================

        $container->set(NoticeService::class, static function (Container $c): NoticeService {
            return new NoticeService(
                $c->get(NoticeRepository::class),
                $c->get(DateFactory::class)
            );
        });

        $container->set(NoticeController::class, static function (Container $c): NoticeController {
            return new NoticeController(
                $c->get(NoticeListAction::class),
                $c->get(NoticeFormAction::class),
                $c->get(NoticeSaveAction::class),
                $c->get(NoticeDeleteAction::class)
            );
        });

        $container->set(NoticeListAction::class, static function (Container $c): NoticeListAction {
            return new NoticeListAction(
                $c->get(NoticeRepository::class),
                $c->get(GroupRepository::class),
                $c->get(WpUserDirectory::class)
            );
        });

        $container->set(NoticeFormAction::class, static function (Container $c): NoticeFormAction {
            return new NoticeFormAction(
                $c->get(NoticeRepository::class),
                $c->get(GroupRepository::class),
                $c->get(WpUserDirectory::class)
            );
        });

        $container->set(NoticeSaveAction::class, static function (Container $c): NoticeSaveAction {
            return new NoticeSaveAction(
                $c->get(NoticeRepository::class),
                $c->get(NoticeService::class),
                $c->get(DateFactory::class)
            );
        });

        $container->set(NoticeDeleteAction::class, static function (Container $c): NoticeDeleteAction {
            return new NoticeDeleteAction(
                $c->get(NoticeService::class)
            );
        });

        // ====================================================================
        // GROUP MODULE COMPOUNDS
        // ====================================================================

        $container->set(GroupService::class, static function (Container $c): GroupService {
            return new GroupService(
                $c->get(GroupRepository::class)
            );
        });

        $container->set(GroupController::class, static function (Container $c): GroupController {
            return new GroupController(
                $c->get(GroupListAction::class),
                $c->get(GroupFormAction::class),
                $c->get(GroupSaveAction::class),
                $c->get(GroupDeleteAction::class)
            );
        });

        $container->set(GroupListAction::class, static function (Container $c): GroupListAction {
            return new GroupListAction(
                $c->get(GroupRepository::class)
            );
        });

        $container->set(GroupFormAction::class, static function (Container $c): GroupFormAction {
            return new GroupFormAction(
                $c->get(GroupRepository::class)
            );
        });

        $container->set(GroupSaveAction::class, static function (Container $c): GroupSaveAction {
            return new GroupSaveAction(
                $c->get(GroupRepository::class),
                $c->get(GroupService::class),
                $c->get(DateFactory::class)
            );
        });

        $container->set(GroupDeleteAction::class, static function (Container $c): GroupDeleteAction {
            return new GroupDeleteAction(
                $c->get(GroupService::class)
            );
        });
    }
}