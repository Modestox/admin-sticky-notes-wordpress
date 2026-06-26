<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Shared\Ui\Component;

/**
 * Immutable HTTP query payload interceptor responsible for extracting, sanitizing, and formatting request state filters.
 */
final class FilterBuilder
{
    /**
     * Internal state containing sanitized and processed filter pairs.
     *
     * @var array<string, mixed>
     */
    private array $filters = [];

    /**
     * Extracts a key-based internal identifier or state token filter safely.
     */
    public function key(string $queryParam, string $filterKey): self
    {
        if (!empty($_GET[$queryParam])) {
            $clone = clone $this;
            $clone->filters[$filterKey] = sanitize_key($_GET[$queryParam]);
            return $clone;
        }

        return $this;
    }

    /**
     * Extracts and thoroughly cleans a standard text or broad query search segment filter.
     */
    public function text(string $queryParam, string $filterKey): self
    {
        if (!empty($_GET[$queryParam])) {
            $clone = clone $this;
            $clone->filters[$filterKey] = sanitize_text_field($_GET[$queryParam]);
            return $clone;
        }

        return $this;
    }

    /**
     * Extracts an implicit strict entity index integer or scalar identifier boundary filter.
     */
    public function integer(string $queryParam, string $filterKey): self
    {
        if (isset($_GET[$queryParam]) && $_GET[$queryParam] !== '') {
            $clone = clone $this;
            $clone->filters[$filterKey] = (int)$_GET[$queryParam];
            return $clone;
        }

        return $this;
    }

    /**
     * Compiles and extracts complete array maps containing all populated runtime filter parameters.
     *
     * @return array<string, mixed> Map of fully processed field-to-value specifications.
     */
    public function build(): array
    {
        return $this->filters;
    }
}