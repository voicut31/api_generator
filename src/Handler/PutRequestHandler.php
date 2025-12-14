<?php

declare(strict_types=1);

namespace ApiGenerator\Handler;

use ApiGenerator\Contract\RequestHandlerInterface;
use ApiGenerator\Contract\SchemaInterface;

/**
 * Class PutRequestHandler
 * Handles PUT and PATCH requests
 *
 * @package ApiGenerator\Handler
 */
class PutRequestHandler implements RequestHandlerInterface
{
    private SchemaInterface $schema;

    public function __construct(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Handle PUT/PATCH request
     *
     * @param string|null $module
     * @param int|string|null $id
     * @param array $params
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function handle(?string $module, int|string|null $id, array $params): array
    {
        $this->schema->update($module, $id, $params);
        return ['message' => 'ok'];
    }

    /**
     * Check if handler supports PUT or PATCH method
     *
     * @param string $method
     * @return bool
     */
    public function supports(string $method): bool
    {
        return in_array($method, ['PUT', 'PATCH']);
    }
}

