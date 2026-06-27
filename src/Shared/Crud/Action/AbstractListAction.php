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

use Modestox\AdminStickyNotes\Shared\Ui\Component\FilterBuilder;
use Modestox\AdminStickyNotes\Shared\Ui\GridRenderer;
use Modestox\AdminStickyNotes\Shared\Ui\Component\AbstractGrid;

/**
 * Structural abstraction orchestrating declarative pagination, query sort sanitization, and dataset grid presentation.
 */
abstract readonly class AbstractListAction
{
    /**
     * Executes baseline HTTP GET query parameters intercept, requests datasets and triggers tabular UI renderers.
     */
    public function execute(): void
    {
        $gridDefinition = $this->getGridDefinition();

        $orderBy = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '';
        $direction = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

        $dbOrderBy = $this->mapOrderByField($orderBy);
        $filters = $this->configureFilters(new FilterBuilder())->build();

        $perPage = (int)get_option('modestox_adminstickynotes_general_grid_page_limit', 10) ?: 10;
        $currentPage = isset($_GET['paged']) ? max(1, (int)$_GET['paged']) : 1;
        $offset = ($currentPage - 1) * $perPage;

        $totalItems = $this->getTotalItemsCount($filters);
        $entities = $this->loadEntities($dbOrderBy, $direction, $perPage, $offset, $filters);
        $gridData = $this->prepareGridRows($entities);

        $renderer = $this->createGridRenderer($gridDefinition, $gridData, $totalItems, $perPage, $orderBy, $direction);
        $renderer->prepare_items();

        echo '<div class="wrap">';
        echo sprintf('<h1 class="wp-heading-inline">%s</h1>', esc_html($gridDefinition->getTitle()));
        echo sprintf('<a href="?page=%s&action=new" class="page-title-action">%s</a>', esc_attr($_GET['page'] ?? ''), esc_html($this->getAddNewLabel()));
        echo '<hr class="wp-header-end">';

        echo sprintf('<form method="get" action="%s">', esc_url(admin_url('admin.php')));
        echo sprintf('<input type="hidden" name="page" value="%s" />', esc_attr($_GET['page'] ?? ''));

        if (!empty($orderBy)) {
            echo sprintf('<input type="hidden" name="orderby" value="%s" />', esc_attr($orderBy));
            echo sprintf('<input type="hidden" name="order" value="%s" />', esc_attr(strtolower($direction)));
        }

        foreach ($filters as $filterKey => $filterValue) {
            if ($filterValue !== null && $filterValue !== '') {
                echo sprintf('<input type="hidden" name="filter_%s" value="%s" />', esc_attr($filterKey), esc_attr((string)$filterValue));
            }
        }

        $renderer->display();
        echo '</form></div>';
    }

    abstract protected function getTotalItemsCount(array $filters): int;
    abstract protected function loadEntities(string $orderBy, string $direction, int $perPage, int $offset, array $filters): array;
    abstract public function getGridDefinition(): AbstractGrid;
    abstract public function getAddNewLabel(): string;
    abstract public function mapOrderByField(string $orderBy): string;
    abstract public function configureFilters(FilterBuilder $builder): FilterBuilder;
    abstract public function prepareGridRows(array $entities): array;

    /**
     * Теперь метод требует передачи параметров сортировки.
     */
    abstract public function createGridRenderer(
        AbstractGrid $gridDefinition,
        array $gridData,
        int $totalItems,
        int $perPage,
        string $orderBy,
        string $direction
    ): GridRenderer;
}