<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Group\Repository; // 🔥 New strict domain namespace

/**
 * Handles persistent database operations for Sticky Note Groups.
 */
final readonly class GroupRepository
{
    private string $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'modestox_sticky_note_groups';
    }

    /**
     * Fetches all registered groups mapped as an associative ID to Title registry.
     *
     * @return array<int, string>
     */
    public function getLookupPairs(): array
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            "SELECT id, title FROM {$this->tableName} ORDER BY sort_order ASC, title ASC",
            ARRAY_A
        );

        if (!is_array($rows)) {
            return [];
        }

        $pairs = [];
        foreach ($rows as $row) {
            $pairs[(int)$row['id']] = (string)$row['title'];
        }

        return $pairs;
    }
}