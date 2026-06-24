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
    /**
     * Active structural table layout column blueprints specification map.
     *
     * @var array<int, Column>
     */
    private array $columnsSchema;

    /**
     * Total items found in database for current query boundaries.
     */
    private int $totalItemsCount;

    /**
     * Maximum allowed records shown simultaneously inside single slice view.
     */
    private int $perPageLimit;

    /**
     * Runtime Constructor mapping entity collections configuration layers.
     *
     * @param array<int, Column> $columnsSchema Blueprint specification maps layout setup.
     * @param array<int, array<string, mixed>> $items Renderable associative grid collection database stack data.
     * @param int $totalItemsCount Total count for calculated pagination limits.
     * @param int $perPageLimit Items count slice constraint per view page.
     */
    public function __construct(array $columnsSchema, array $items = [], int $totalItemsCount = 0, int $perPageLimit = 10)
    {
        parent::__construct([
            'singular' => 'ui_item',
            'plural'   => 'ui_items',
            'ajax'     => false,
        ]);

        $this->columnsSchema = $columnsSchema;
        $this->items = $items;
        $this->totalItemsCount = $totalItemsCount;
        $this->perPageLimit = $perPageLimit;
    }

    /**
     * Obligatory WordPress core abstraction contract override.
     *
     * @return void
     */
    public function prepare_items(): void
    {
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];

        $this->set_pagination_args([
            'total_items' => $this->totalItemsCount,
            'per_page'    => $this->perPageLimit,
            'total_pages' => (int)ceil($this->totalItemsCount / $this->perPageLimit),
        ]);
    }

    /**
     * Returns standard structural list array maps representing columns configuration keys.
     *
     * @return array<string, string>
     */
    public function get_columns(): array
    {
        $columns = [];
        foreach ($this->columnsSchema as $column) {
            $columns[$column->id] = $column->label;
        }
        return $columns;
    }

    /**
     * Defines sortable parameters metadata context schema.
     *
     * @return array<string, array{0: string, 1: bool}>
     */
    protected function get_sortable_columns(): array
    {
        $sortable = [];
        foreach ($this->columnsSchema as $column) {
            if ($column->isSortable) {
                $sortable[$column->id] = [$column->id, false];
            }
        }
        return $sortable;
    }

    /**
     * Fallback macro interceptor parsing specific layout content generation cells dynamically.
     *
     * @param array<string, mixed> $item Single item data row package.
     * @param string $column_name Raw column identifier string key.
     * @return string
     */
    protected function column_default($item, $column_name): string
    {
        $value = $item[$column_name] ?? '';

        if ($value instanceof \DateTimeImmutable) {
            return esc_html($value->format('Y-m-d H:i'));
        }

        if (is_string($value) || is_numeric($value)) {
            return (string)$value;
        }

        return '';
    }
}