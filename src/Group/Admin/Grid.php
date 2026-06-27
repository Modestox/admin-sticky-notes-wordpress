<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Group\Admin;

use Modestox\AdminStickyNotes\Shared\Ui\Component\AbstractGrid;
use Modestox\AdminStickyNotes\Shared\Ui\Component\Column;

/**
 * Declarative administrative data grid structural configuration blueprint for Groups.
 */
final readonly class Grid extends AbstractGrid
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return __('Admin Sticky Note Groups', 'modestox-admin-sticky-notes');
    }

    /**
     * @inheritDoc
     */
    protected function defineColumns(): array
    {
        return [
            new Column('id', __('ID', 'modestox-admin-sticky-notes'), true),
            new Column('title', __('Group Title', 'modestox-admin-sticky-notes'), true),
            new Column('slug', __('URL Slug', 'modestox-admin-sticky-notes'), true),
            new Column('allowedRoles', __('Allowed Roles', 'modestox-admin-sticky-notes')),
            new Column('sortOrder', __('Sort Order', 'modestox-admin-sticky-notes'), true, 'number'),
            new Column('createdAt', __('Created At', 'modestox-admin-sticky-notes'), true, 'datetime'),
            new Column('actions', __('Actions', 'modestox-admin-sticky-notes')),
        ];
    }
}