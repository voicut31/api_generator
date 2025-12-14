<?php

declare(strict_types=1);

namespace ApiGenerator\Handler;

use ApiGenerator\Contract\RequestHandlerInterface;
use ApiGenerator\Contract\SchemaInterface;

/**
 * Class PostRequestHandler
 * Handles POST requests
 *
 * @package ApiGenerator\Handler
 */
class PostRequestHandler implements RequestHandlerInterface
{
    private SchemaInterface $schema;

    public function __construct(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Handle POST request
     *
     * @param string|null $module
     * @param int|string|null $id
     * @param array $params
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function handle(?string $module, int|string|null $id, array $params): array
    {
        $this->schema->insert($module, $params);
        return ['message' => 'ok'];
    }

    /**
     * Check if handler supports POST method
     *
     * @param string $method
     * @return bool
     */
    public function supports(string $method): bool
    {
        return $method === 'POST';
    }
}

