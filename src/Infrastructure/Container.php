<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Infrastructure;

/**
 * Lightweight explicit Dependency Injection Container utilizing closure factories.
 */
final class Container
{
    /**
     * Storage for instantiated singleton services.
     *
     * @var array<string, object>
     */
    private array $instances = [];

    /**
     * Storage for registered closure service factories.
     *
     * @var array<string, \Closure>
     */
    private array $factories = [];

    /**
     * Registers a service definition factory closure.
     *
     * @param string $id Explicit class name or interface target string identifier.
     * @param \Closure $factory Execution factory block responsible for returning the target object.
     * @return void
     */
    public function set(string $id, \Closure $factory): void
    {
        $this->factories[$id] = $factory;

        // Clear runtime cache instances if definition changes dynamically
        unset($this->instances[$id]);
    }

    /**
     * Resolves and returns a cached singleton instance of the requested service.
     *
     * @template T of object
     * @param class-string<T> $id Explicit fully qualified class name target string identifier.
     * @return T Fully hydrated operational dependency instance.
     * @throws \InvalidArgumentException Triggered if target class mapping cannot be found in factories.
     */
    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->factories[$id])) {
            throw new \InvalidArgumentException(
                sprintf('Requested dependency compound object structure "%s" is not registered inside the container factories.', $id),
            );
        }

        // Invoke the closure factory passing this container context for internal wiring resolution
        $factory = $this->factories[$id];
        $instance = $factory($this);

        $this->instances[$id] = $instance;
        return $instance;
    }
}