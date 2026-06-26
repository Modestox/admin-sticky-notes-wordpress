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
        /** @var AbstractGrid $gridDefinition */
        $gridDefinition = $this->getGridDefinition();

        $orderBy = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'id';
        $direction = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';
        $dbOrderBy = $this->mapOrderByField($orderBy);

        $filters = $this->configureFilters(new FilterBuilder())->build();

        $configKey = 'modestox_adminstickynotes_general_grid_page_limit';
        $perPage = (int)get_option($configKey, 10);
        if ($perPage <= 0) {
            $perPage = 10;
        }

        $currentPage = isset($_GET['paged']) ? max(1, (int)$_GET['paged']) : 1;
        $offset = ($currentPage - 1) * $perPage;

        // 🔥 Loaded via clean polymorphic delegation methods
        $totalItems = $this->getTotalItemsCount($filters);
        $entities = $this->loadEntities($dbOrderBy, $direction, $perPage, $offset, $filters);

        $gridData = $this->prepareGridRows($entities);

        $renderer = $this->createGridRenderer($gridDefinition, $gridData, $totalItems, $perPage);
        $renderer->prepare_items();

        echo '<div class="wrap">';
        echo sprintf('<h1 class="wp-heading-inline">%s</h1>', esc_html($gridDefinition->getTitle()));
        echo sprintf(
            '<a href="?page=%s&action=new" class="page-title-action">%s</a>',
            esc_attr($_GET['page'] ?? ''),
            esc_html($this->getAddNewLabel()),
        );
        echo '<hr class="wp-header-end">';

        echo sprintf('<form method="get" action="%s">', esc_url(admin_url('admin.php')));
        echo sprintf('<input type="hidden" name="page" value="%s" />', esc_attr($_GET['page'] ?? ''));

        $renderer->display();

        echo '</form>';
        echo '</div>';
    }

    /**
     * Returns the total amount of matched records based on active filter rules.
     */
    abstract protected function getTotalItemsCount(array $filters): int;

    /**
     * Loads a specific slice of domain model entities collection listings.
     *
     * @return array<int, object>
     */
    abstract protected function loadEntities(string $orderBy, string $direction, int $perPage, int $offset, array $filters): array;

    /**
     * Returns domain structural layout blueprint metadata configuration object.
     */
    abstract public function getGridDefinition(): AbstractGrid;

    /**
     * Returns localized action interaction text button string.
     */
    abstract public function getAddNewLabel(): string;

    /**
     * Resolves incoming column identifiers to precise physical database mapping fields.
     */
    abstract public function mapOrderByField(string $orderBy): string;

    /**
     * Chains schema configuration parameters filters rules on top of provided builder fluent instance.
     */
    abstract public function configureFilters(FilterBuilder $builder): FilterBuilder;

    /**
     * Iterates over loaded Domain DTO entities translating them to flat displayable string payload maps.
     *
     * @param array<int, object> $entities Raw structural entities collection list.
     * @return array<int, array<string, mixed>> Compiles target tabular displayable rows data.
     */
    abstract public function prepareGridRows(array $entities): array;

    /**
     * Assembles completely operational and scoped instance adapter bridge table viewer.
     */
    abstract public function createGridRenderer(AbstractGrid $gridDefinition, array $gridData, int $totalItems, int $perPage): GridRenderer;
}