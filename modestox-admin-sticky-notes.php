<?php
/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

/**
 * Plugin Name: Modestox Admin Sticky Notes
 * Description: Internal board with state-version notifications and sticky notes for WordPress admin panel.
 * Version:     1.0.0
 * Author:      Sergey Kuzmitsky
 * License:     MIT
 * Requires PHP: 8.3
 * Requires Plugins: modestox-config-processor-wp
 * Text Domain:  modestox-admin-sticky-notes
 * Domain Path:  /languages
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes;

if (!defined('ABSPATH')) {
    exit;
}

// Initialize Composer Autoloader
$autoloader = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

/**
 * Register the isolated plugin activation hook.
 * Strictly triggers one-time database migration using the isolated service.
 */
register_activation_hook(__FILE__, [Plugin::class, 'activate']);

/**
 * Returns the main operational instance of the Sticky Notes plugin.
 * Replaces old-school $GLOBALS entries with a clean, type-hinted function wrapper.
 *
 * @return Plugin
 */
function modestoxStickyNotes(): Plugin
{
    return Plugin::instance();
}

// Bootstrap the plugin lifecycle on WordPress initialization
add_action('plugins_loaded', static function (): void {
    modestoxStickyNotes()->boot();
});