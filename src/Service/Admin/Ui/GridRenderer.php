<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Service\Admin\Ui;

use Modestox\AdminStickyNotes\Service\Admin\Ui\Component\Column;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Universal layout factory adapter bridging abstract core components with standard native platforms.
 */
final class GridRenderer extends \WP_List_Table
{
    /**
     * Map of indexed columns configurations for fast O(1) random access reads.
     *
     * @var array<string, Column>
     */
    private array $indexedSchema = [];

    /**
     * @param array<int, Column> $columnsSchema
     * @param array<int, array<string, mixed>> $dataset
     */
    public function __construct(
        private array $columnsSchema,
        array $dataset,
    ) {
        parent::__construct([
            'singular' => 'ui_item',
            'plural'   => 'ui_items',
            'ajax'     => false,
        ]);

        $this->items = $dataset;
        $this->indexColumnsSchema();
    }

    /**
     * Obligatory WordPress core abstraction contract override.
     * Since our dataset is pre-hydrated into memory, we declare execution complete.
     *
     * @return void
     */
    public function prepare_items(): void
    {
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
    }

    /**
     * Maps abstract metadata items to system structural columns.
     *
     * @return array<string, string>
     */
    public function get_columns(): array
    {
        $mapped = [];
        foreach ($this->columnsSchema as $column) {
            $mapped[$column->id] = $column->label;
        }
        return $mapped;
    }

    /**
     * Defines sortable parameters metadata context schema.
     *
     * @return array<string, array{0: string, 1: bool}>
     */
    protected function get_sortable_columns(): array
    {
        return [];
    }

    /**
     * Special row action override dedicated specifically for the 'title' column slot.
     * Generates native absolute paths with inline state confirmations.
     *
     * @param array<string, mixed> $item
     * @return string
     */
    protected function column_title(array $item): string
    {
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        return sprintf(
            '<strong><a class="row-title" href="%s">%s</a></strong>',
            esc_url(admin_url('admin.php?page=' . $page . '&action=edit&id=' . $item['id'])),
            esc_html($item['title'])
        );
    }

    /**
     * Explicitly renders raw HTML actions buffer without escaping layout elements.
     *
     * @param array<string, mixed> $item
     * @return string
     */
    protected function column_actions(array $item): string
    {
        // 🔥 Returns pre-compiled actions markup from Controller raw and fully working
        return $item['actions'] ?? '';
    }

    /**
     * Renders column layout cells based on concrete registered metadata type configurations.
     * Optimized from nested O(N*M) loop searching to absolute O(1) hash table access map.
     *
     * @param array<string, mixed> $item
     * @param string $columnName
     * @return string
     */
    protected function column_default($item, $columnName): string
    {
        $value = $item[$columnName] ?? '';
        $column = $this->indexedSchema[$columnName] ?? null;

        if ($column === null) {
            return esc_html((string)$value);
        }

        return match ($column->type) {
            'badge'    => sprintf(
                '<span class="modestox-badge modestox-badge-%s" style="background:#e5e5e5;padding:3px 8px;border-radius:3px;font-weight:600;">%s</span>',
                esc_attr((string)$value),
                esc_html((string)$value),
            ),
            'datetime' => $value instanceof \DateTimeImmutable ? $value->format('Y-m-d H:i') : esc_html((string)$value),
            default    => esc_html((string)$value),
        };
    }

    /**
     * Suppresses WordPress native row actions injection engine overlay inside the primary column bounds.
     *
     * @return string
     */
    protected function get_default_primary_column_name(): string
    {
        // 🔥 Returning a non-existent or standalone key completely breaks off core row-actions injections
        return 'none';
    }

    /**
     * Converts indexed schemas into associative structures once during execution boot sequences.
     *
     * @return void
     */
    private function indexColumnsSchema(): void
    {
        foreach ($this->columnsSchema as $column) {
            $this->indexedSchema[$column->id] = $column;
        }
    }
}