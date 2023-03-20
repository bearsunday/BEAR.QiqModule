<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use Qiq\Escape;
use Qiq\Exception;
use Qiq\Helper;

use function array_merge;
use function class_exists;
use function function_exists;

class HelperLocator extends \Qiq\HelperLocator
{
    public static function new(Escape|null $escape = null, array $factories = []): static
    {
        $escape ??= new Escape('utf-8');

        return new static(array_merge([
            'a'      => static fn () => new Helper\EscapeAttr($escape),
            'c'      => static fn () => new Helper\EscapeCss($escape),
            'escape' => static fn () => $escape,
            'h'      => static fn () => new Helper\EscapeHtml($escape),
            'u'      => static fn () => new Helper\EscapeUrl($escape),
        ], $factories), $escape);
    }

    /** @var array<object> */
    protected array $instances = [];
    protected Escape $escape;

    public function __construct(protected array $factories = [], Escape|null $escape = null)
    {
        $this->escape = $escape ?: new Escape('utf-8');
    }

    public function __call(string $name, array $args): mixed
    {
        return $this->get($name)(...$args);
    }

    public function set(string $name, mixed /* callable */ $callable): void
    {
        $this->factories[$name] = $callable;
        unset($this->instances[$name]);
    }

    public function has(string $name): bool
    {
        if (isset($this->factories[$name]) || isset($this->instances[$name])) {
            return true;
        }

        $func = '\\' . $name;

        if (function_exists($name)) {
            $this->instances[$name] = $func;

            return true;
        }

        $maybeHelper = 'Qiq\Helper\\' . $name;
        if (class_exists($maybeHelper)) {
            if (! isset($this->factories[$name])) {
                $this->factories[$name] = fn () => new $maybeHelper($this->escape);
            }

            return true;
        }

        return false;
    }

    public function get(string $name): mixed
    {
        if (! $this->has($name)) {
            throw new Exception\HelperNotFound($name);
        }

        if (! isset($this->instances[$name])) {
            $factory = $this->factories[$name];
            $this->instances[$name] = $factory();
        }

        return $this->instances[$name];
    }
}
