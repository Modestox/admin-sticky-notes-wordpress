<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Exception\Handler;

/**
 * Centralized exception catcher handling unexpected runtime failures.
 */
final readonly class PluginErrorHandler
{
    /**
     * Catches and logs throwables to avoid breaking the global WordPress core engine execution.
     *
     * @param \Throwable $exception Standard caught executable failure token descriptor.
     * @return void
     */
    public function handle(\Throwable $exception): void
    {
        $message = sprintf(
            '[Modestox Admin Sticky Notes Error]: %s in %s on line %d. Stack Trace: %s',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        error_log($message);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_action('admin_notices', static function () use ($exception): void {
                echo sprintf(
                    '<div class="notice notice-error"><p><strong>Modestox Fault:</strong> %s</p></div>',
                    esc_html($exception->getMessage())
                );
            });
        }
    }
}