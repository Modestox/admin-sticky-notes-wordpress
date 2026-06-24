<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Infrastructure\Database;

/**
 * Handles synchronous database schema deployment during plugin activation.
 */
final class Installer
{
    /**
     * Executes the baseline database table structure initialization.
     *
     * @return void
     */
    public function install(): void
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $groupsTable = $wpdb->prefix . 'modestox_sticky_note_groups';
        $notesTable  = $wpdb->prefix . 'modestox_sticky_notes';

        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        // 1. Schema for structural group categorizations
        $groupsSql = "CREATE TABLE $groupsTable (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            slug varchar(64) NOT NULL,
            title varchar(255) NOT NULL,
            allowed_roles text NOT NULL,
            sort_order int(11) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug),
            KEY sort_order (sort_order)
        ) $charsetCollate;";

        // 2. Schema for core sticky note entities and their target tracking relations
        $notesSql = "CREATE TABLE $notesTable (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            group_id varchar(2048) NOT NULL DEFAULT '0',
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            target_user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            title varchar(255) NOT NULL DEFAULT '',
            content text NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'draft',
            priority varchar(20) NOT NULL DEFAULT 'normal',
            start_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            updated_at datetime NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY group_id (group_id(191)),
            KEY user_id (user_id),
            KEY target_user_id (target_user_id),
            KEY status (status),
            KEY start_date_end_date (start_date, end_date)
        ) $charsetCollate;";

        dbDelta($groupsSql);
        dbDelta($notesSql);
    }

    /**
     * Completely drops plugin tables from the database during uninstallation.
     *
     * @return void
     */
    public function uninstall(): void
    {
        global $wpdb;

        $groupsTable = $wpdb->prefix . 'modestox_sticky_note_groups';
        $notesTable  = $wpdb->prefix . 'modestox_sticky_notes';

        $wpdb->query("DROP TABLE IF EXISTS {$notesTable};");
        $wpdb->query("DROP TABLE IF EXISTS {$groupsTable};");
    }
}