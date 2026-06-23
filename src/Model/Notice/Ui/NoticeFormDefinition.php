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

use Modestox\AdminStickyNotes\Service\Admin\Ui\Component\Field;
use Modestox\AdminStickyNotes\Service\Admin\Ui\Component\FieldOption;

/**
 * Declarative single source of truth form structural layout metadata definition for Notices.
 */
final readonly class NoticeFormDefinition
{
    /**
     * Compiles and returns the schema layout fields configuration with full i18n support.
     *
     * @return array<int, Field>
     */
    public function getFields(): array
    {
        return [
            Field::text('title', __('Notice Title', 'modestox-admin-sticky-notes'), true),
            Field::textarea('message', __('Notice Content / Message', 'modestox-admin-sticky-notes'), true),
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
        ];
    }
}