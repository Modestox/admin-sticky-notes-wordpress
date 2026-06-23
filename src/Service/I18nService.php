<?php

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Service;

/**
 * Infrastructure service responsible for initializing and loading
 * internationalization (i18n) textdomains for the plugin.
 */
final readonly class I18nService
{
    /**
     * Registers the plugin textdomain within the WordPress localization subsystem.
     */
    public function loadTextdomain(): void
    {
        $pluginBaseDir = dirname(__DIR__, 2);

        load_plugin_textdomain(
            'modestox-admin-sticky-notes',
            false,
            plugin_basename($pluginBaseDir) . '/languages',
        );
    }
}