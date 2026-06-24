<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Notice\Ui;

use Modestox\AdminStickyNotes\Shared\Ui\Component\Field;
use Modestox\AdminStickyNotes\Shared\Ui\Component\FieldOption;

/**
 * Declarative single source of truth form structural layout metadata definition for Notices.
 */
final readonly class NoticeFormDefinition
{
    /**
     * Compiles and returns the schema layout fields configuration with full i18n support.
     *
     * @param array<int, string> $groupPairs Dynamically injected lookup pairs from the database.
     * @return array<int, Field>
     */
    public function getFields(array $groupPairs = []): array
    {
        $groupOptions = [
            new FieldOption('0', __('— All Groups —', 'modestox-admin-sticky-notes')),
        ];

        foreach ($groupPairs as $id => $title) {
            $groupOptions[] = new FieldOption((string)$id, $title);
        }

        return [
            Field::text('title', __('Notice Title', 'modestox-admin-sticky-notes'), true),
            Field::textarea('message', __('Notice Content / Message', 'modestox-admin-sticky-notes'), true),
            Field::multiselect('groupId', __('Target Groups', 'modestox-admin-sticky-notes'), $groupOptions, true),

            Field::select('priority', __('Urgency Priority', 'modestox-admin-sticky-notes'), [
                new FieldOption('low', __('Low Importance', 'modestox-admin-sticky-notes')),
                new FieldOption('normal', __('Regular Normal', 'modestox-admin-sticky-notes')),
                new FieldOption('high', __('High Priority', 'modestox-admin-sticky-notes')),
                new FieldOption('critical', __('Critical / Immediate Action', 'modestox-admin-sticky-notes')),
            ], true),

            Field::select('status', __('Lifecycle Status', 'modestox-admin-sticky-notes'), [
                new FieldOption('draft', __('Draft (Hidden)', 'modestox-admin-sticky-notes')),
                new FieldOption('publish', __('Published (Active)', 'modestox-admin-sticky-notes')),
                new FieldOption('archived', __('Archived', 'modestox-admin-sticky-notes')),
            ], true),

            Field::datetime('startDate', __('Execution Start Date', 'modestox-admin-sticky-notes'), true),
            Field::datetime('endDate', __('Execution End Date', 'modestox-admin-sticky-notes'), true),
        ];
    }
}