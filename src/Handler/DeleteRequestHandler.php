<?php

declare(strict_types=1);

namespace ApiGenerator\Handler;

use ApiGenerator\Contract\RequestHandlerInterface;
use ApiGenerator\Contract\SchemaInterface;

/**
 * Class DeleteRequestHandler
 * Handles DELETE requests
 *
 * @package ApiGenerator\Handler
 */
class DeleteRequestHandler implements RequestHandlerInterface
{
    private SchemaInterface $schema;

    public function __construct(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Handle DELETE request
     *
     * @param string|null $module
     * @param int|string|null $id
     * @param array $params
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function handle(?string $module, int|string|null $id, array $params): array
    {
        $this->schema->delete($module, $id);
        return [];
    }

    /**
     * Check if handler supports DELETE method
     *
     * @param string $method
     * @return bool
     */
    public function supports(string $method): bool
    {
        return $method === 'DELETE';
    }
}

