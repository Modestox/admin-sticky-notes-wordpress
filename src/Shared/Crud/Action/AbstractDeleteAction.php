<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Shared\Crud\Action;

/**
 * Reusable blueprint encapsulating secure administrative entity eviction mutations.
 */
abstract readonly class AbstractDeleteAction
{
    /**
     * Orchestrates standard safe record elimination with cryptographic token checks.
     */
    public function execute(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', $this->getNonceActionName($id))) {
            wp_die(esc_html__('Security execution verification failed.', 'modestox-admin-sticky-notes'));
        }

        $this->performDelete($id);

        wp_redirect(admin_url($this->getRedirectUrl()));
        exit;
    }

    /**
     * Delegates the actual destruction process to the specific domain layer handling (e.g. Domain Service).
     */
    abstract protected function performDelete(int $id): void;

    /**
     * Returns unique cryptographic token identifier context string.
     */
    abstract protected function getNonceActionName(int $id): string;

    /**
     * Compiles standard post-mutation destination administrative query string.
     */
    abstract protected function getRedirectUrl(): string;
}