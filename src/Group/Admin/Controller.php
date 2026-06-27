<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Group\Admin;

use Modestox\AdminStickyNotes\Shared\Crud\AbstractCrudController;
use Modestox\AdminStickyNotes\Group\Admin\Action\ListAction;
use Modestox\AdminStickyNotes\Group\Admin\Action\FormAction;
use Modestox\AdminStickyNotes\Group\Admin\Action\SaveAction;
use Modestox\AdminStickyNotes\Group\Admin\Action\DeleteAction;

/**
 * Thin orchestrator routing lifecycle requests directly to granular group action classes.
 */
final class Controller extends AbstractCrudController
{
    /**
     * Injecting single action services using DI constructor promotion.
     */
    public function __construct(
        private ListAction $listAction,
        private FormAction $formAction,
        private SaveAction $saveAction,
        private DeleteAction $deleteAction,
    ) {}

    /**
     * @inheritDoc
     */
    protected function renderGridAction(): void
    {
        $this->listAction->execute();
    }

    /**
     * @inheritDoc
     */
    protected function renderFormAction(): void
    {
        $this->formAction->execute();
    }

    /**
     * @inheritDoc
     */
    protected function saveAction(): void
    {
        $this->saveAction->execute();
    }

    /**
     * @inheritDoc
     */
    protected function deleteAction(): void
    {
        $this->deleteAction->execute();
    }
}