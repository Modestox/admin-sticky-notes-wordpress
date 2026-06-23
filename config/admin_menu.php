<?php

declare(strict_types=1);

return [
    'parent'   => [
        'page_title' => __('Admin Sticky Notices', 'modestox-admin-sticky-notes'),
        'menu_title' => __('Admin Sticky Notices', 'modestox-admin-sticky-notes'),
        'capability' => 'manage_options',
        'menu_slug'  => 'modestox-admin-sticky-notes',
        'icon_url'   => 'dashicons-admin-post',
        'position'   => 30,
        'controller' => \Modestox\AdminStickyNotes\Controller\Admin\NoticeController::class,
    ],
    'submenus' => [
        [
            'page_title' => __('Admin Sticky Notices Pool', 'modestox-admin-sticky-notes'),
            'menu_title' => __('All Notices', 'modestox-admin-sticky-notes'),
            'capability' => 'manage_options',
            'menu_slug'  => 'modestox-admin-sticky-notes',
            'action'     => 'list',
            'controller' => \Modestox\AdminStickyNotes\Controller\Admin\NoticeController::class,
        ],
        [
            'page_title' => __('Create New Administrative Notice', 'modestox-admin-sticky-notes'),
            'menu_title' => __('Add New Notice', 'modestox-admin-sticky-notes'),
            'capability' => 'manage_options',
            'menu_slug'  => 'modestox-notices-new',
            'action'     => 'new',
            'controller' => \Modestox\AdminStickyNotes\Controller\Admin\NoticeController::class,
        ],
    ],
];