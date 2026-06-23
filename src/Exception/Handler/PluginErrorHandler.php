<?php

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Exception\Handler;

/**
 * High-level infrastructure layer handler responsible for processing unexpected runtime faults
 * and anomalies depending on the active WordPress execution environment.
 */
final readonly class PluginErrorHandler
{
    /**
     * Inspects the exception context and terminates or logs the fault safely.
     * Re-throws the exception if the environment context is unrecognizable.
     *
     * @throws \Throwable
     */
    public function handle(\Throwable $exception): void
    {
        // 1. Always log to standard server error log stream
        error_log(sprintf('[Modestox Admin Sticky Notes] %s', $exception->getMessage()));

        // 2. WP-CLI execution support context
        if (defined('WP_CLI') && \WP_CLI) {
            \WP_CLI::error($exception->getMessage());
            return;
        }

        // 3. REST API & AJAX contexts - use safe native WordPress JSON wrapper
        if ($this->isRestRequest() || (defined('DOING_AJAX') && \DOING_AJAX)) {
            wp_send_json_error([
                'error'   => 'Component Configuration Fault',
                'message' => $exception->getMessage(),
            ], 500);
        }

        // 4. Background Cron queue worker context
        if (defined('DOING_CRON') && \DOING_CRON) {
            return;
        }

        // 5. Native Admin UI fallback display screen
        if (is_admin()) {
            wp_die(
                esc_html($exception->getMessage()),
                'Modestox Component Error',
                [
                    'response'  => 500,
                    'back_link' => true,
                ],
            );
        }

        // 6. Final safety net: if context is completely unknown, re-throw to crash loudly and safely
        throw $exception;
    }

    /**
     * Evaluates whether the current environment transaction targets REST endpoints.
     */
    private function isRestRequest(): bool
    {
        return defined('REST_REQUEST') && \REST_REQUEST;
    }
}