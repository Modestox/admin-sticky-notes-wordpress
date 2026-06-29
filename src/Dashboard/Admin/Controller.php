<?php
/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Dashboard\Admin;

use Modestox\AdminStickyNotes\Dashboard\Admin\Action\ListAction;

final readonly class Controller
{
    public function __construct(
        private ListAction $listAction,
    ) {}

    public function execute(): void
    {
        $data = $this->listAction->execute();

        if (empty($data)) {
            echo "Dashboard data is empty.";
        }

        $this->render('dashboard-list', [
            'data' => $data,
        ]);
    }

    private function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $templatePath = __DIR__ . "/View/{$template}.php";

        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template not found: {$templatePath}");
        }

        require $templatePath;
    }
}