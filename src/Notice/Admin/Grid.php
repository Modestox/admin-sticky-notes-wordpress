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

use Modestox\AdminStickyNotes\Shared\Ui\Component\AbstractGrid;
use Modestox\AdminStickyNotes\Shared\Ui\Component\Column;

/**
 * Declarative administrative data grid structural configuration blueprint for Notices.
 */
final readonly class Grid extends AbstractGrid
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return __('Admin Notices Pool', 'modestox-admin-sticky-notes');
    }

    /**
     * @inheritDoc
     */
    protected function defineColumns(): array
    {
        return [
            new Column('id', __('ID', 'modestox-admin-sticky-notes'), true),
            new Column('title', __('Title', 'modestox-admin-sticky-notes'), true),
            new Column('groupName', __('Target Group', 'modestox-admin-sticky-notes')),
            new Column('targetUser', __('Target User', 'modestox-admin-sticky-notes')),
            new Column('status', __('Status', 'modestox-admin-sticky-notes'), true, 'badge'),
            new Column('priority', __('Priority', 'modestox-admin-sticky-notes'), true, 'badge'),
            new Column('startDate', __('Start Date', 'modestox-admin-sticky-notes'), true, 'datetime'),
            new Column('endDate', __('End Date', 'modestox-admin-sticky-notes'), true, 'datetime'),
            new Column('actions', __('Actions', 'modestox-admin-sticky-notes')),
        ];
    }
}