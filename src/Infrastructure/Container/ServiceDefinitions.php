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

/**
 * Explicit configuration registry dedicated exclusively for business domain core services.
 */
final class ServiceDefinitions implements ContainerDefinitionInterface
{
    /**
     * @inheritDoc
     */
    public static function register(Container $container): void
    {
        // Сюда будут инжектиться чисто бизнес-сервисы по мере роста платформы:
        // $container->set(ExportService::class, static fn(Container $c) => new ExportService($c->get(NoticeRepository::class)));
    }
}