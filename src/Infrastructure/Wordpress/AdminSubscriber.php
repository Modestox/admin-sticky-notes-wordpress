<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Infrastructure\Wordpress;

use Modestox\AdminStickyNotes\Config\AdminMenuRegistry;

/**
 * Isolated infrastructure subscriber responsible for binding administrative
 * WordPress hooks and routing them to correct business logic definitions.
 */
final readonly class AdminSubscriber
{
    /**
     * Dependency injection handled via constructor instantiation.
     */
    public function __construct(
        private AdminMenuRegistry $menuRegistry,
    ) {}

    /**
     * Binds all necessary administrative filters and actions into the WordPress ecosystem.
     *
     * @return void
     */
    public function listen(): void
    {
        add_filter('modestox_register_admin_plugin_config', [$this, 'registerAdminPages']);
        add_action('admin_menu', [$this, 'registerAdminMenus']);
        add_action('admin_init', [$this, 'handleAdminMutations']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    /**
     * Enqueues module dedicated CSS layouts and vanilla Javascript dynamic rows components safely.
     *
     * @param string $hook Current administrative page context screen tracker.
     * @return void
     */
    public function enqueueAdminAssets(string $hook): void
    {
        $assetsUrl = plugin_dir_url(dirname(__DIR__, 2));

        wp_enqueue_style(
            'modestox-admin-sticky-notes-css',
            $assetsUrl . 'assets/css/admin.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'modestox-admin-sticky-notes-js',
            $assetsUrl . 'assets/js/admin.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }

    /**
     * Appends the module settings page to the global config processor registry.
     *
     * @param array<int, array<string, mixed>> $pages
     * @return array<int, array<string, mixed>>
     */
    public function registerAdminPages(array $pages): array
    {
        $pageConfig = $this->menuRegistry->getSettingsPageConfig();

        if (!empty($pageConfig)) {
            $pages[] = $pageConfig;
        }

        return $pages;
    }

    /**
     * Intercepts and processes data mutations (save, delete) before any HTML is emitted.
     *
     * @return void
     */
    public function handleAdminMutations(): void
    {
        $currentPage = $_GET['page'] ?? '';
        if ($currentPage !== 'modestox-admin-sticky-notes' && $currentPage !== 'modestox-notices-new') {
            return;
        }

        $action = isset($_GET['action']) ? (string)$_GET['action'] : '';
        if ($action !== 'save' && $action !== 'delete') {
            return;
        }

        $schema = $this->menuRegistry->getAdminMenu();
        $controllerClass = $schema['parent']['controller'] ?? null;

        if ($controllerClass !== null) {
            \Modestox\AdminStickyNotes\modestoxStickyNotes()
                ->getContainer()
                ->get($controllerClass)
                ->execute($action);
        }
    }

    /**
     * Registers menu links structural layouts inside WordPress admin panel for view rendering only.
     *
     * @return void
     */
    public function registerAdminMenus(): void
    {
        $schema = $this->menuRegistry->getAdminMenu();
        if (empty($schema)) {
            return;
        }

        $parent = $schema['parent'] ?? null;
        if ($parent === null) {
            return;
        }

        add_menu_page(
            $parent['page_title'],
            $parent['menu_title'],
            $parent['capability'],
            $parent['menu_slug'],
            function () use ($parent): void {
                $action = isset($_GET['action']) ? (string)$_GET['action'] : 'list';

                if ($action === 'save' || $action === 'delete') {
                    return;
                }

                /** @var class-string $controllerClass */
                $controllerClass = $parent['controller'];

                \Modestox\AdminStickyNotes\modestoxStickyNotes()
                    ->getContainer()
                    ->get($controllerClass)
                    ->execute($action);
            },
            $parent['icon_url'],
            $parent['position'],
        );

        $submenus = $schema['submenus'] ?? [];
        foreach ($submenus as $submenu) {
            $isDuplicateOfParent = ($submenu['menu_slug'] === $parent['menu_slug']);

            $callback = $isDuplicateOfParent ? '__return_false' : function () use ($submenu): void {
                $action = isset($_GET['action']) ? (string)$_GET['action'] : ($submenu['action'] ?? 'list');

                if ($action === 'save' || $action === 'delete') {
                    return;
                }

                /** @var class-string $controllerClass */
                $controllerClass = $submenu['controller'];

                \Modestox\AdminStickyNotes\modestoxStickyNotes()
                    ->getContainer()
                    ->get($controllerClass)
                    ->execute($action);
            };

            add_submenu_page(
                $parent['menu_slug'],
                $submenu['page_title'],
                $submenu['menu_title'],
                $submenu['capability'],
                $submenu['menu_slug'],
                $callback,
            );
        }
    }
}