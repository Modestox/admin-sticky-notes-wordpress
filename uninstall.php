<?php
/**
 * Modestox Admin Sticky Notes - Uninstall Script
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

use Modestox\AdminStickyNotes\Service\Database\Installer;

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$autoloader = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

try {
    $installer = new Installer();
    $installer->uninstall();
} catch (\Throwable $e) {
    error_log(sprintf('[Modestox Admin Sticky Notes Uninstall Fault] %s', $e->getMessage()));
}