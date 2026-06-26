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
 * Baseline template handling structural sanitization, payload assembly and entity persistence lifecycle.
 */
abstract readonly class AbstractSaveAction
{
    /**
     * Processes submission payloads, verifies nonces, mutates state and triggers structural persistence.
     */
    public function execute(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            wp_die(esc_html__('Invalid request method mapping context.', 'modestox-admin-sticky-notes'));
        }

        $nonceField = $this->getNonceFieldName();
        if (!wp_verify_nonce($_POST[$nonceField] ?? '', $this->getNonceActionName())) {
            wp_die(esc_html__('Security verification failed during persistence routine.', 'modestox-admin-sticky-notes'));
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $existingEntity = $this->loadExistingEntity($id);

        if ($id !== null && $existingEntity === null) {
            wp_die(esc_html__('Target operational record not found.', 'modestox-admin-sticky-notes'));
        }

        $entity = $this->buildEntityFromRequest($_POST, $existingEntity);

        $this->persist($entity);

        wp_redirect(admin_url($this->getRedirectUrl()));
        exit;
    }

    /**
     * Delegates the actual entity lookup to specific child modules before hydration rules apply.
     */
    abstract protected function loadExistingEntity(?int $id): ?object;

    /**
     * Delegates the final validation and persistence to the domain layer (e.g., Domain Service).
     */
    abstract protected function persist(object $entity): void;

    /**
     * Returns targeted structural cryptographic token verification key marker.
     */
    abstract public function getNonceActionName(): string;

    /**
     * Returns the specific POST request array key name representing the nonce token layout field.
     */
    abstract public function getNonceFieldName(): string;

    /**
     * Compiles final safe destination operational page redirect token string.
     */
    abstract public function getRedirectUrl(): string;

    /**
     * Assembles completely hydrated safe domain object representation from actual payload states.
     *
     * @param array<string, mixed> $requestData Flat unfiltered raw form submission array collection maps.
     * @param object|null $existingEntity Reference to preloaded active persistent state model if present.
     * @return object Completely configured operational type compliant domain model entity descriptor.
     */
    abstract public function buildEntityFromRequest(array $requestData, ?object $existingEntity): object;
}