<?php

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Infrastructure\Container;

use Modestox\AdminStickyNotes\Infrastructure\Container;
use Modestox\AdminStickyNotes\Dashboard\Infrastructure\ContainerRegistration as DashboardRegistration;
use Modestox\AdminStickyNotes\Notice\Infrastructure\ContainerRegistration as NoticeRegistration;
use Modestox\AdminStickyNotes\Group\Infrastructure\ContainerRegistration as GroupRegistration;
use Modestox\AdminStickyNotes\Shared\Infrastructure\ContainerRegistration as SharedRegistration;

final class ControllerDefinitions implements ContainerRegistrationInterface
{
    public static function register(Container $container): void
    {
        // Register shared infrastructure dependencies first
        SharedRegistration::register($container);

        // Register individual module components
        DashboardRegistration::register($container);
        NoticeRegistration::register($container);
        GroupRegistration::register($container);


        // Here you can manually register any global service if needed
        // Example: $container->set(NoticeListAction::class, static function (Container $c): NoticeListAction {
        //            return new NoticeListAction(
        //                $c->get(NoticeRepository::class),
        //                $c->get(GroupRepository::class),
        //                $c->get(WpUserDirectory::class)
        //            );
        //        });
    }
}