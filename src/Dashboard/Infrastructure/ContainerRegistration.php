<?php
/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Dashboard\Infrastructure;

use Modestox\AdminStickyNotes\Infrastructure\Container;
use Modestox\AdminStickyNotes\Infrastructure\Container\ContainerRegistrationInterface;
use Modestox\AdminStickyNotes\Dashboard\Admin\Action\ListAction;
use Modestox\AdminStickyNotes\Dashboard\Admin\Controller;
use Modestox\AdminStickyNotes\Group\Repository\GroupRepository;
use Modestox\AdminStickyNotes\Notice\Repository\NoticeRepository;
use Modestox\AdminStickyNotes\Shared\Helper\DateFactory;

final class ContainerRegistration implements ContainerRegistrationInterface
{
    public static function register(Container $container): void
    {
        $container->set(ListAction::class, static fn(Container $c) => new ListAction(
            $c->get(GroupRepository::class),
            $c->get(NoticeRepository::class),
            $c->get(DateFactory::class)
        ));

        $container->set(Controller::class, static fn(Container $c) => new Controller(
            $c->get(ListAction::class)
        ));
    }
}