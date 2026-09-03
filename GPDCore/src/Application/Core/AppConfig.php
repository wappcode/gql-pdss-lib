<?php

declare(strict_types=1);

namespace GPDCore\Application\Core;

use GPDCore\Contracts\AppConfigInterface;

final class AppConfig implements AppConfigInterface
{
    private static ?self $instance = null;
    private array $config = [];
    private array $masterConfig = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->masterConfig[$key])) {
            $baseValue = $this->config[$key] ?? null;

            if (is_array($this->masterConfig[$key]) && is_array($baseValue)) {
                return array_replace_recursive($baseValue, $this->masterConfig[$key]);
            }

            return $this->masterConfig[$key];
        }

        return $this->config[$key] ?? $default;
    }

    public function add(array $newConfig): self
    {
        $this->config = array_replace_recursive($this->config, $newConfig);

        return $this;
    }

    public function setMasterConfig(array $masterConfig): self
    {
        $this->masterConfig = array_replace_recursive($this->masterConfig, $masterConfig);

        return $this;
    }

    private function __construct()
    {
    }

    private function __clone(): void
    {
    }

    public function __wakeup(): void
    {
    }
}
