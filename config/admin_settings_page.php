<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
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
                    'info_work'      => [
                        'type'   => 'infoblock',
                        'text'   => __('Краткое описанние', 'modestox-admin-sticky-notes'),
                        'format' => 'html',
                    ],
                    'enable_logging' => [
                        'type'    => 'yes_no',
                        'default' => 1,
                    ],
                ],
            ],
        ],
    ],
];