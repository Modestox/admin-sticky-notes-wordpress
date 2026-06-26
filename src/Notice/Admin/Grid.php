<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Notice\Admin;

use Modestox\AdminStickyNotes\Shared\Ui\Component\Column;

/**
 * Declarative single source of truth grid structural layout layout definition for Notices.
 */
final readonly class Grid
{
    /**
     * Compiles and returns the schema column mapping definitions with full i18n support.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::text('title', __('Title', 'modestox-admin-sticky-notes'), true),
            Column::text('groupName', __('Group', 'modestox-admin-sticky-notes'), false),
            Column::text('targetUser', __('Target User', 'modestox-admin-sticky-notes'), true),
            Column::badge('status', __('Status', 'modestox-admin-sticky-notes'), true),
            Column::badge('priority', __('Priority', 'modestox-admin-sticky-notes'), true),
            Column::datetime('startDate', __('Start Date', 'modestox-admin-sticky-notes'), true),
            Column::datetime('endDate', __('End Date', 'modestox-admin-sticky-notes'), true),
            Column::text('actions', __('Actions', 'modestox-admin-sticky-notes'), false),
        ];
    }
}