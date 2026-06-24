<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Shared\Crud;

/**
 * Baseline execution abstraction encapsulating request routing, state mutations, and view rendering.
 */
abstract class AbstractCrudController
{
    /**
     * Executes context request routing based on explicitly passed operational actions.
     *
     * @param string $action Target explicit action boundary operation name identifier.
     * @return void
     */
    public function execute(string $action): void
    {
        $action = sanitize_key($action);

        switch ($action) {
            case 'new':
            case 'edit':
                $this->renderFormAction();
                break;

            case 'save':
                $this->saveAction();
                break;

            case 'delete':
                $this->deleteAction();
                break;

            case 'list':
            default:
                $this->renderGridAction();
                break;
        }
    }

    /**
     * Handles the grid viewing execution state.
     */
    abstract protected function renderGridAction(): void;

    /**
     * Handles the creation or update entity form screen state.
     */
    abstract protected function renderFormAction(): void;

    /**
     * Process data mutation and persistence updates from the request payload.
     */
    abstract protected function saveAction(): void;

    /**
     * Processes record eviction out of the database registry state boundaries.
     */
    abstract protected function deleteAction(): void;
}