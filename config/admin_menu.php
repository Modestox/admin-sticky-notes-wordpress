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
    'parent'   => [
        'page_title' => __('Admin Sticky Notices', 'modestox-admin-sticky-notes'),
        'menu_title' => __('Admin Sticky Notices', 'modestox-admin-sticky-notes'),
        'capability' => 'manage_options',
        'menu_slug'  => 'modestox-admin-sticky-notes',
        'icon_url'   => 'dashicons-admin-post',
        'position'   => 30,
        'controller' => \Modestox\AdminStickyNotes\Notice\Ui\NoticeController::class,
    ],
    'submenus' => [
        [
            'page_title' => __('Admin Sticky Notices Pool', 'modestox-admin-sticky-notes'),
            'menu_title' => __('All Notices', 'modestox-admin-sticky-notes'),
            'capability' => 'manage_options',
            'menu_slug'  => 'modestox-admin-sticky-notes',
            'action'     => 'list',
            'controller' => \Modestox\AdminStickyNotes\Notice\Ui\NoticeController::class,
        ],
        [
            'page_title' => __('Create New Administrative Notice', 'modestox-admin-sticky-notes'),
            'menu_title' => __('Add New Notice', 'modestox-admin-sticky-notes'),
            'capability' => 'manage_options',
            'menu_slug'  => 'modestox-notices-new',
            'action'     => 'new',
            'controller' => \Modestox\AdminStickyNotes\Notice\Ui\NoticeController::class,
        ],
    ],
];