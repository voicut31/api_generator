<?php

declare(strict_types=1);

namespace ApiGenerator\Handler;

use ApiGenerator\Contract\RequestHandlerInterface;

/**
 * Class OptionsRequestHandler
 * Handles OPTIONS requests (CORS preflight)
 *
 * @package ApiGenerator\Handler
 */
class OptionsRequestHandler implements RequestHandlerInterface
{
    /**
     * Handle OPTIONS request
     *
     * @param string|null $module
     * @param int|string|null $id
     * @param array $params
     * @return array
     */
    public function handle(?string $module, int|string|null $id, array $params): array
    {
        return [];
    }

    /**
     * Check if handler supports OPTIONS method
     *
     * @param string $method
     * @return bool
     */
    public function supports(string $method): bool
    {
        return $method === 'OPTIONS';
    }
}

