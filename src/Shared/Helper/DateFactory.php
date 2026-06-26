<?php

/**
 * Modestox Admin Sticky Notes
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   MIT
 * @link      https://github.com/Modestox/admin-sticky-notes-wordpress
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Shared\Helper;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Centralized factory handling safe, immutable date-time generation unified with WordPress core timezone configurations.
 */
final class DateFactory
{
    /**
     * Creates a DateTimeImmutable instance automatically localized to the active WordPress environment timezone.
     *
     * @param string|null $datetime Flat raw string value representations (e.g. database datetime snapshots, 'now').
     * @param DateTimeZone|null $timezone Optional override constraint, defaults to wp_timezone() configuration.
     * @return DateTimeImmutable Fully configured immutable date-time instance model mapping.
     * @throws \InvalidArgumentException Triggered if parsing invalid temporal string formats.
     */
    public function create(?string $datetime = null, ?DateTimeZone $timezone = null): DateTimeImmutable
    {
        $targetTimezone = $timezone ?? wp_timezone();

        try {
            return new DateTimeImmutable($datetime ?? 'now', $targetTimezone);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                sprintf('Failed parsing structural dynamic date-time state payload: "%s".', $datetime ?? 'now'),
                0,
                $e
            );
        }
    }

    /**
     * Creates a highly precise DateTimeImmutable snapshot strictly complying with fixed target database formats.
     *
     * @param string $format Targeted validation pattern matching rule syntax layout (e.g. 'Y-m-d H:i:s').
     * @param string $datetime Target chronological execution string context boundaries representation.
     * @param DateTimeZone|null $timezone Optional override constraint, defaults to wp_timezone() configuration.
     * @return DateTimeImmutable Fully validated localized explicit date-time segment mapping model.
     * @throws \InvalidArgumentException Triggered if target dataset layout breaks format rules constraints.
     */
    public function createFromFormat(string $format, string $datetime, ?DateTimeZone $timezone = null): DateTimeImmutable
    {
        $targetTimezone = $timezone ?? wp_timezone();
        $instance = DateTimeImmutable::createFromFormat($format, $datetime, $targetTimezone);

        if ($instance === false) {
            throw new \InvalidArgumentException(
                sprintf('The incoming context sequence "%s" breaks strict template format validation rules: "%s".', $datetime, $format)
            );
        }

        return $instance;
    }
}