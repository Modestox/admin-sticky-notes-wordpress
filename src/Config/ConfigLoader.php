<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Config;

use Modestox\AdminStickyNotes\Exception\PluginException;

/**
 * Centered configuration loader with explicit type-safe API wrapper methods.
 */
final readonly class ConfigLoader
{
    private const string CONFIG_DIR = __DIR__ . '/../../config/';

    private const string FILE_ADMIN_SETTINGS = 'admin_settings_page';
    private const string FILE_ADMIN = 'admin_menu';

    public function adminSettingsPage(): array
    {
        return $this->load(self::FILE_ADMIN_SETTINGS);
    }

    public function adminMenu(): array
    {
        return $this->load(self::FILE_ADMIN);
    }

    /**
     * @throws PluginException
     */
    private function load(string $file): array
    {
        $path = self::CONFIG_DIR . $file . '.php';

        if (!is_file($path)) {
            throw new PluginException(
                sprintf('Critical Error: Required configuration target file "%s.php" is missing.', $file),
            );
        }

        /** @var array<string, mixed> $config */
        $config = require $path;

        return $config;
    }
}