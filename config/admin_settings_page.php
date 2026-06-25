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
                            10  => '10',
                            20  => '20',
                            30  => '30',
                            50  => '50',
                            100 => '100',
                            200 => '200',
                        ],
                        'default' => '10',
                    ],
                    'notes_priority'  => [
                        'type'    => 'dynamic_rows',
                        'label'   => __('Urgency Priority', 'modestox-admin-sticky-notes'),
                        'columns' => [
                            'code'  => 'Code',
                            'title' => 'Title',
                        ],
                        'default' => [
                            [
                                'code'  => 'low',
                                'title' => __('Low', 'modestox-admin-sticky-notes'),
                            ],
                            [
                                'code'  => 'normal',
                                'title' => __('Normal', 'modestox-admin-sticky-notes'),
                            ],
                            [
                                'code'  => 'high',
                                'title' => __('High', 'modestox-admin-sticky-notes'),
                            ],
                            [
                                'code'  => 'critical',
                                'title' => __('Critical', 'modestox-admin-sticky-notes'),
                            ],
                        ],
                    ],
                    'notes_status'    => [
                        'type'    => 'dynamic_rows',
                        'label'   => __('Lifecycle Status', 'modestox-admin-sticky-notes'),
                        'columns' => [
                            'code'  => 'Code',
                            'title' => 'Title',
                        ],
                        'default' => [
                            [
                                'code'  => 'draft',
                                'title' => __('Draft (Hidden)', 'modestox-admin-sticky-notes'),
                            ],
                            [
                                'code'  => 'publish',
                                'title' => __('Published (Active)', 'modestox-admin-sticky-notes'),
                            ],
                            [
                                'code'  => 'archived',
                                'title' => __('Archived', 'modestox-admin-sticky-notes'),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];