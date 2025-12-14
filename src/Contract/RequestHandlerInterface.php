<?php

declare(strict_types=1);

namespace ApiGenerator\Contract;

/**
 * Interface RequestHandlerInterface
 * @package ApiGenerator\Contract
 */
interface RequestHandlerInterface
{
    /**
     * Handle a request
     *
     * @param string|null $module
     * @param int|string|null $id
     * @param array $params
     * @return array
     */
    public function handle(?string $module, int|string|null $id, array $params): array;

    /**
     * Check if handler supports this method
     *
     * @param string $method
     * @return bool
     */
    public function supports(string $method): bool;
}

