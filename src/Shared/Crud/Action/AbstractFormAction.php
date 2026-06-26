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

use Modestox\AdminStickyNotes\Shared\Ui\FormRenderer;
use Modestox\AdminStickyNotes\Shared\Ui\Component\AbstractForm;
use Modestox\AdminStickyNotes\Shared\Ui\Component\Field;

/**
 * Baseline view abstraction encapsulating secure entity preloading, data conversion and declarative form rendering.
 */
abstract readonly class AbstractFormAction
{
    /**
     * Validates operational payload identities, preloads models if present and renders standard views layouts.
     */
    public function execute(): void
    {
        $formDefinition = $this->getFormDefinition();
        $renderer = new FormRenderer();

        $formData = [];
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if ($id !== null) {
            $entity = $this->loadEntity($id);
            if ($entity) {
                $formData = $this->mapEntityToFormData($entity);
            }
        }

        $formActionUrl = admin_url(
            sprintf(
                'admin.php?page=%s&action=save%s',
                sanitize_key($_GET['page'] ?? ''),
                $id ? '&id=' . $id : '',
            ),
        );

        echo '<div class="wrap">';
        echo sprintf(
            '<h1>%s</h1>',
            $id ? esc_html($this->getEditTitle()) : esc_html($this->getCreateTitle())
        );
        echo sprintf('<form method="post" action="%s">', esc_url($formActionUrl));

        wp_nonce_field($this->getNonceActionName(), $this->getNonceFieldName());

        $renderer->render($this->getFormFields($formDefinition), $formData);

        submit_button($id ? esc_html($this->getUpdateLabel()) : esc_html($this->getSaveLabel()));
        echo '</form>';
        echo '</div>';
    }

    /**
     * Delegates targeted specific structural preloading operation down to the specific child module layer.
     */
    abstract protected function loadEntity(int $id): ?object;

    /**
     * Returns structural configuration form mapper.
     */
    abstract public function getFormDefinition(): AbstractForm;

    /**
     * Compiles complete fields schemas with bound external contextual datasets lookups.
     *
     * @return array<int, Field>
     */
    abstract public function getFormFields(object $formDefinition): array;

    /**
     * Converts a loaded domain object state values to a flat key-value UI compatible layout map.
     */
    abstract public function mapEntityToFormData(object $entity): array;

    /** @return string Localized edit heading context text string title. */
    abstract public function getEditTitle(): string;

    /** @return string Localized create heading context text string title. */
    abstract public function getCreateTitle(): string;

    /** @return string Cryptographic token validation context reference key. */
    abstract public function getNonceActionName(): string;

    /** @return string The specific POST request array key name representing the nonce token layout field. */
    abstract public function getNonceFieldName(): string;

    /** @return string Localized form mutation update submission action text button layout. */
    abstract public function getUpdateLabel(): string;

    /** @return string Localized form mutation create submission action text button layout. */
    abstract public function getSaveLabel(): string;
}