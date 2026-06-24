<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

return [
    'plugin'      => 'Modestox_AdminStickyNotes',
    'parent_slug' => 'modestox-admin-sticky-notes',
    'page_slug'   => 'mtx-admin-sticky-notes-settings',
    'menu_title'  => __('Settings', 'modestox-admin-sticky-notes'),
    'capability'  => 'manage_options',
    'schema'      => [
        'groups' => [
            'general' => [
                'label'  => __('General', 'modestox-admin-sticky-notes'),
                'fields' => [
                    'grid_page_limit' => [
                        'type'    => 'select',
                        'label'   => __('Page limit', 'modestox-admin-sticky-notes'),
                        'options' => [
                            5 => '5',
                            10 => '10',
                            20 => '20',
                        ],
                        'default' => '10',
                    ],
                ],
            ],
        ],
    ],
];