<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes;

use Modestox\AdminStickyNotes\Config\AdminMenuRegistry;
use Modestox\AdminStickyNotes\Config\ConfigLoader;
use Modestox\AdminStickyNotes\Exception\Handler\PluginErrorHandler;
use Modestox\AdminStickyNotes\Infrastructure\Wordpress\AdminSubscriber;
use Modestox\AdminStickyNotes\Infrastructure\Wordpress\I18nService;
use Modestox\AdminStickyNotes\Infrastructure\Database\Installer;
use Modestox\AdminStickyNotes\Infrastructure\Container;
use Modestox\AdminStickyNotes\Infrastructure\ContainerConfigurator;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main bootstrap class for the Modestox Admin Sticky Notes plugin.
 */
final class Plugin
{
    private static ?self $instance = null;
    private bool $isBooted = false;

    private AdminSubscriber $adminSubscriber;
    private I18nService $i18nService;
    private PluginErrorHandler $errorHandler;
    private Container $container;

    private function __construct()
    {
        $this->registerServices();
    }

    /**
     * Returns the global singleton instance of the plugin bootstrap.
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Context execution for one-time plugin activation logic.
     */
    public static function activate(): void
    {
        try {
            $installer = new Installer();
            $installer->install();
        } catch (\Throwable $e) {
            error_log(sprintf('[Modestox Activation Fault] Database install failure: %s', $e->getMessage()));

            wp_die(
                'Plugin activation failed due to database setup error: ' . esc_html($e->getMessage()),
                'Activation Error',
                ['response' => 500],
            );
        }
    }

    /**
     * Orchestrates the boot sequence and hooks of the plugin.
     */
    public function boot(): void
    {
        if ($this->isBooted) {
            return;
        }
        $this->isBooted = true;

        try {
            $this->i18nService->loadTextdomain();

            if (is_admin()) {
                $this->adminSubscriber->listen();
            }
        } catch (\Throwable $e) {
            $this->errorHandler->handle($e);
        }
    }

    /**
     * Returns the dedicated business domain isolated service container handler instance.
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Instantiates internal bootstrap infrastructure configuration handlers.
     */
    private function registerServices(): void
    {
        $configLoader = new ConfigLoader();
        $menuRegistry = new AdminMenuRegistry($configLoader);

        $this->container = new Container();
        ContainerConfigurator::configure($this->container);

        $this->adminSubscriber = new AdminSubscriber($menuRegistry);
        $this->i18nService = new I18nService();
        $this->errorHandler = new PluginErrorHandler();
    }

    public function __clone(): void
    {
        throw new \LogicException('Cloning of a singleton instance is strictly prohibited.');
    }

    public function __wakeup(): void
    {
        throw new \LogicException('Unserializing of a singleton instance is prohibited.');
    }
}