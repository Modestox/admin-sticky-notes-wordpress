<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Shared\Ui;

if (!class_exists(\WP_List_Table::class)) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use Modestox\AdminStickyNotes\Shared\Ui\Component\Column;

/**
 * Generic structural adapter bridge rendering datasets via standard WordPress table layouts.
 */
final class GridRenderer extends \WP_List_Table
{
    private array $columnsSchema;
    private array $indexedSchema = [];
    private int $totalItemsCount;
    private int $perPageLimit;
    private ?\Closure $filterCallback;
    private string $currentOrderby;
    private string $currentOrder;

    public function __construct(
        array $columnsSchema,
        array $items = [],
        int $totalItemsCount = 0,
        int $perPageLimit = 10,
        string $currentOrderby = '',
        string $currentOrder = 'desc',
        ?\Closure $filterCallback = null,
    ) {
        parent::__construct([
            'singular' => 'ui_item',
            'plural'   => 'ui_items',
            'ajax'     => false,
        ]);

        $this->columnsSchema = $columnsSchema;
        $this->items = $items;
        $this->totalItemsCount = $totalItemsCount;
        $this->perPageLimit = $perPageLimit;
        $this->currentOrderby = $currentOrderby;
        $this->currentOrder = $currentOrder;
        $this->filterCallback = $filterCallback;

        foreach ($columnsSchema as $column) {
            $this->indexedSchema[$column->id] = $column;
        }
    }

    public function prepare_items(): void
    {
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        $this->set_pagination_args([
            'total_items' => $this->totalItemsCount,
            'per_page'    => $this->perPageLimit,
            'total_pages' => (int)ceil($this->totalItemsCount / $this->perPageLimit),
        ]);
    }

    public function get_pagenum_link($pagenum = 1, $escape = true): string
    {
        $url = parent::get_pagenum_link($pagenum, false);

        if (!empty($this->currentOrderby)) {
            $url = add_query_arg([
                'orderby' => $this->currentOrderby,
                'order'   => $this->currentOrder,
            ], $url);
        }

        return $escape ? esc_url($url) : $url;
    }

    public function get_columns(): array
    {
        $columns = [];
        foreach ($this->columnsSchema as $column) {
            $columns[$column->id] = $column->label;
        }
        return $columns;
    }

    protected function get_sortable_columns(): array
    {
        $sortable = [];
        foreach ($this->columnsSchema as $column) {
            if ($column->isSortable) {
                $isSorted = ($this->currentOrderby === $column->id);
                $sortable[$column->id] = [$column->id, $isSorted];
            }
        }
        return $sortable;
    }

    protected function column_title(array $item): string
    {
        $rowId = isset($item['id']) ? (int)$item['id'] : 0;
        $editUrl = admin_url(sprintf('admin.php?page=%s&action=edit&id=%d', sanitize_key($_GET['page'] ?? ''), $rowId));
        return sprintf('<a href="%s" class="row-title"><strong>%s</strong></a>', esc_url($editUrl), esc_html((string)($item['title'] ?? '')));
    }

    protected function column_default($item, $column_name): string
    {
        $value = $item[$column_name] ?? '';
        $column = $this->indexedSchema[$column_name] ?? null;

        if ($column_name === 'actions') {
            return (string)$value;
        }
        if ($column?->type === 'datetime' && $value instanceof \DateTimeInterface) {
            return esc_html($value->format((string)get_option('date_format') . ' ' . (string)get_option('time_format')));
        }
        if ($column?->type === 'badge') {
            return sprintf('<span class="modestox-badge mtx-badge-%s">%s</span>', esc_attr((string)$value), esc_html((string)$value));
        }
        return esc_html((string)$value);
    }

    protected function extra_tablenav($which): void
    {
        if ($which === 'top' && $this->filterCallback !== null) {
            ($this->filterCallback)();
        }
    }
}