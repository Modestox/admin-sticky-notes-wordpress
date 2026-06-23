<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Model\Notice\Ui;

use Modestox\AdminStickyNotes\Service\Admin\Ui\Component\Column;

/**
 * Declarative single source of truth grid structural layout layout definition for Notices.
 */
final readonly class NoticeGridDefinition
{
    /**
     * Compiles and returns the schema column mapping definitions with full i18n support.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::text('id', __('ID', 'modestox-admin-sticky-notes'), true),
            Column::text('title', __('Title', 'modestox-admin-sticky-notes'), true),
            Column::badge('priority', __('Priority', 'modestox-admin-sticky-notes'), false),
            Column::badge('status', __('Status', 'modestox-admin-sticky-notes'), true),
            Column::datetime('createdAt', __('Created At', 'modestox-admin-sticky-notes'), true),
        ];
    }
}