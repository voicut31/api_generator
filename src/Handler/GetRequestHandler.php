<?php

declare(strict_types=1);

namespace ApiGenerator\Handler;

use ApiGenerator\Contract\RequestHandlerInterface;
use ApiGenerator\Contract\SchemaInterface;

/**
 * Class GetRequestHandler
 * Handles GET requests
 *
 * @package ApiGenerator\Handler
 */
class GetRequestHandler implements RequestHandlerInterface
{
    private SchemaInterface $schema;

    public function __construct(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Handle GET request
     *
     * @param string|null $module
     * @param int|string|null $id
     * @param array $params
     * @return array
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function handle(?string $module, int|string|null $id, array $params): array
    {
        if ($id !== null) {
            return $this->schema->getResult($module, $id);
        }

        return $this->schema->getResults($module);
    }

    /**
     * Check if handler supports GET method
     *
     * @param string $method
     * @return bool
     */
    public function supports(string $method): bool
    {
        return $method === 'GET';
    }
}

