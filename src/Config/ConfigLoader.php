<?php

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
    private const string FILE_PERMISSIONS = 'permissions';
    private const string FILE_NOTIFICATIONS = 'notifications';

    public function adminSettingsPage(): array
    {
        return $this->load(self::FILE_ADMIN_SETTINGS);
    }

    public function adminMenu(): array
    {
        return $this->load(self::FILE_ADMIN);
    }

    public function permissions(): array
    {
        return $this->load(self::FILE_PERMISSIONS);
    }

    public function notifications(): array
    {
        return $this->load(self::FILE_NOTIFICATIONS);
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