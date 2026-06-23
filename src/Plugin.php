<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes;

use Modestox\AdminStickyNotes\Config\AdminMenuRegistry;
use Modestox\AdminStickyNotes\Config\ConfigLoader;
use Modestox\AdminStickyNotes\Exception\Handler\PluginErrorHandler;
use Modestox\AdminStickyNotes\Service\AdminSubscriber;
use Modestox\AdminStickyNotes\Service\I18nService;
use Modestox\AdminStickyNotes\Service\Database\Installer;
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
     *
     * @return self
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Instantiates internal bootstrap infrastructure configuration handlers.
     *
     * @return void
     */
    private function registerServices(): void
    {
        $configLoader = new ConfigLoader();
        $menuRegistry = new AdminMenuRegistry($configLoader);

        // 1. Initialize clean independent container wrapper engine
        $this->container = new Container();

        // 2. Delegate dependency wiring mapping definitions to external configurator
        ContainerConfigurator::configure($this->container);

        $this->adminSubscriber = new AdminSubscriber($menuRegistry);
        $this->i18nService = new I18nService();
        $this->errorHandler = new PluginErrorHandler();
    }

    /**
     * Cloning of a singleton instance is strictly prohibited.
     *
     * @return void
     * @throws \LogicException Always.
     */
    public function __clone(): void
    {
        throw new \LogicException('Cloning of a singleton instance is strictly prohibited.');
    }

    /**
     * Unserializing of a singleton instance is strictly prohibited.
     *
     * @return void
     * @throws \LogicException Always.
     */
    public function __wakeup(): void
    {
        throw new \LogicException('Unserializing of a singleton instance is strictly prohibited.');
    }
}