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

use Modestox\AdminStickyNotes\Shared\Ui\Component\Field;
use Modestox\AdminStickyNotes\Shared\Ui\Component\FieldOption;

/**
 * Declarative single source of truth form structural layout metadata definition for Notices.
 */
final readonly class Form
{
    /**
     * Compiles and returns the schema layout fields configuration with full i18n support.
     *
     * @param array<int, string> $groupPairs Dynamically injected lookup pairs from the database.
     * @param array<int, string> $userPairs Dynamically injected lookup pairs of WordPress users.
     * @return array<int, Field>
     */
    public function getFields(array $groupPairs = [], array $userPairs = []): array
    {
        $groupOptions = [
            new FieldOption('0', __('— All Groups —', 'modestox-admin-sticky-notes')),
        ];

        foreach ($groupPairs as $id => $title) {
            $groupOptions[] = new FieldOption((string)$id, $title);
        }

        $userOptions = [
            new FieldOption('0', __('— Broad Broadcast (No User Target) —', 'modestox-admin-sticky-notes')),
        ];

        foreach ($userPairs as $id => $displayName) {
            $userOptions[] = new FieldOption((string)$id, $displayName);
        }

        $priorityOptions = [];
        foreach (self::getPriorityPairs() as $code => $title) {
            $priorityOptions[] = new FieldOption((string)$code, $title);
        }

        $statusOptions = [];
        foreach (self::getStatusPairs() as $code => $title) {
            $statusOptions[] = new FieldOption((string)$code, $title);
        }

        return [
            Field::text('title', __('Notice Title', 'modestox-admin-sticky-notes'), true),
            Field::textarea('message', __('Notice Content / Message', 'modestox-admin-sticky-notes'), true),
            Field::multiselect('groupId', __('Target Groups', 'modestox-admin-sticky-notes'), $groupOptions, true),

            Field::select('targetUserId', __('Target Specific User', 'modestox-admin-sticky-notes'), $userOptions, false),

            Field::select('priority', __('Urgency Priority', 'modestox-admin-sticky-notes'), $priorityOptions, false),
            Field::select('status', __('Lifecycle Status', 'modestox-admin-sticky-notes'), $statusOptions, false),

            Field::datetime('startDate', __('Execution Start Date', 'modestox-admin-sticky-notes'), true),
            Field::datetime('endDate', __('Execution End Date', 'modestox-admin-sticky-notes'), true),
        ];
    }

    /**
     * Returns dynamic options map for statuses built from Config Settings.
     *
     * @return array<string, string>
     */
    public static function getStatusPairs(): array
    {
        $configValue = get_option('modestox_adminstickynotes_general_notes_status');
        $rows = is_array($configValue) ? $configValue : (array)maybe_unserialize($configValue);

        if (empty($rows)) {
            return [
                'draft'    => 'Draft (Hidden)',
                'publish'  => 'Published (Active)',
                'archived' => 'Archived',
            ];
        }

        $pairs = [];
        foreach ($rows as $row) {
            if (is_array($row) && isset($row['code'], $row['title']) && trim((string)$row['code']) !== '') {
                $cleanKey = (string)$row['code'];
                $pairs[sanitize_key($cleanKey)] = esc_html((string)$row['title']);
            }
        }

        return $pairs;
    }

    /**
     * Returns dynamic options map for priorities built from Config Settings.
     *
     * @return array<string, string>
     */
    public static function getPriorityPairs(): array
    {
        $configValue = get_option('modestox_adminstickynotes_general_notes_priority');
        $rows = is_array($configValue) ? $configValue : (array)maybe_unserialize($configValue);

        if (empty($rows)) {
            return [
                'low'      => 'Low',
                'normal'   => 'Normal',
                'high'     => 'High',
                'critical' => 'Critical',
            ];
        }

        $pairs = [];
        foreach ($rows as $row) {
            if (is_array($row) && isset($row['code'], $row['title']) && trim((string)$row['code']) !== '') {
                $cleanKey = (string)$row['code'];
                $pairs[sanitize_key($cleanKey)] = esc_html((string)$row['title']);
            }
        }

        return $pairs;
    }
}