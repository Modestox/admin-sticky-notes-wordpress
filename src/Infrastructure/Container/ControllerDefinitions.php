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
use Modestox\AdminStickyNotes\Notice\Admin\Action\ListAction;
use Modestox\AdminStickyNotes\Notice\Admin\Action\FormAction;
use Modestox\AdminStickyNotes\Notice\Admin\Action\SaveAction;
use Modestox\AdminStickyNotes\Notice\Admin\Action\DeleteAction;
use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Infrastructure\Wordpress\WpUserDirectory;
use Modestox\AdminStickyNotes\Notice\Service\NoticeService;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;
use Modestox\AdminStickyNotes\Notice\Admin\Controller as NoticeController;

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
        // Domain Services
        $container->set(NoticeService::class, static function (Container $c): NoticeService {
            return new NoticeService(
                $c->get(NoticeRepository::class),
                $c->get(DateFactory::class)
            );
        });

        // Administrative Controllers
        $container->set(NoticeController::class, static function (Container $c): NoticeController {
            return new NoticeController(
                $c->get(ListAction::class),
                $c->get(FormAction::class),
                $c->get(SaveAction::class),
                $c->get(DeleteAction::class)
            );
        });

        // Action Workflows
        $container->set(ListAction::class, static function (Container $c): ListAction {
            return new ListAction(
                $c->get(NoticeRepository::class),
                $c->get(GroupRepository::class),
                $c->get(WpUserDirectory::class)
            );
        });

        $container->set(FormAction::class, static function (Container $c): FormAction {
            return new FormAction(
                $c->get(NoticeRepository::class),
                $c->get(GroupRepository::class),
                $c->get(WpUserDirectory::class)
            );
        });

        $container->set(SaveAction::class, static function (Container $c): SaveAction {
            return new SaveAction(
                $c->get(NoticeRepository::class),
                $c->get(NoticeService::class),
                $c->get(DateFactory::class)
            );
        });

        $container->set(DeleteAction::class, static function (Container $c): DeleteAction {
            return new DeleteAction(
                $c->get(NoticeService::class)
            );
        });
    }
}