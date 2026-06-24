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
    'page_title' => __('Sticky Notes Settings', 'modestox-admin-sticky-notes'),
    'menu_title' => __('Sticky Notes', 'modestox-admin-sticky-notes'),
    'capability' => 'manage_options',
    'menu_slug'  => 'modestox-sticky-notes-settings',
    'icon_url'   => '',
    'position'   => null,
    'sections'   => [
        [
            'id'     => 'general_settings',
            'title'  => __('General Settings', 'modestox-admin-sticky-notes'),
            'fields' => [
                [
                    'id'          => 'enable_marquee',
                    'type'        => 'checkbox',
                    'title'       => __('Enable Global Marquee Alert', 'modestox-admin-sticky-notes'),
                    'description' => __(
                        'Display critical notices as a running text marquee line across admin header.',
                        'modestox-admin-sticky-notes',
                    ),
                    'default'     => '0',
                ],
                [
                    'id'          => 'refresh_interval',
                    'type'        => 'number',
                    'title'       => __('AJAX Polling Refresh Interval', 'modestox-admin-sticky-notes'),
                    'description' => __('Interval in seconds to pull active sticky updates from database.', 'modestox-admin-sticky-notes'),
                    'default'     => '60',
                ],
            ],
        ],
    ],
];