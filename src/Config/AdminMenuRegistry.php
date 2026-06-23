<?php

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Config;

/**
 * High-level registry coordinator for administrative menus.
 * Leverages type-safe methods from ConfigLoader to securely fetch operational templates.
 */
final readonly class AdminMenuRegistry
{
    /**
     * Dependency injection handled via constructor instantiation.
     */
    public function __construct(
        private ConfigLoader $configLoader,
    ) {}

    /**
     * Requests the explicit admin settings page configuration layer.
     *
     * @return array<string, mixed>
     */
    public function getSettingsPageConfig(): array
    {
        return $this->configLoader->adminSettingsPage();
    }

    /**
     * Compiles and returns the administrative navigation links schema layout.
     *
     * @return array<string, mixed>
     */
    public function getAdminMenu(): array
    {
        return $this->configLoader->adminMenu();
    }
}