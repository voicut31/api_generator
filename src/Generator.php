<?php

declare(strict_types=1);

namespace ApiGenerator;

use ApiGenerator\Contract\ResponseInterface;
use ApiGenerator\Contract\SchemaInterface;
use ApiGenerator\Handler\DeleteRequestHandler;
use ApiGenerator\Handler\GetRequestHandler;
use ApiGenerator\Handler\OptionsRequestHandler;
use ApiGenerator\Handler\PostRequestHandler;
use ApiGenerator\Handler\PutRequestHandler;
use ApiGenerator\Service\ApiStructureBuilder;

/**
 * Class Generator
 * Main entry point for API generation and handling
 *
 * @package ApiGenerator
 */
class Generator
{
    private SchemaInterface $schema;
    private ResponseInterface $response;
    private array $apiStructure = [];

    /**
     * Generator constructor.
     *
     * @param SchemaInterface $schema
     * @param ResponseInterface $response
     */
    public function __construct(SchemaInterface $schema, ResponseInterface $response)
    {
        $this->schema = $schema;
        $this->response = $response;
    }

    /**
     * Generate API structure and handle request
     *
     * @param string|null $module
     * @param int|string|null $id
     * @param array $params
     * @return void
     */
    public function api(?string $module, int|string|null $id = null, array $params = []): void
    {
        $this->generate();
        $this->handleResponse($module, $id, $params);
    }

    /**
     * Generate API structure from database schema
     *
     * @return void
     */
    public function generate(): void
    {
        $builder = new ApiStructureBuilder($this->schema);
        $this->apiStructure = $builder->build();
    }

    /**
     * Get the generated API structure
     *
     * @return array
     */
    public function getApiStructure(): array
    {
        return $this->apiStructure;
    }

    /**
     * Handle the API response
     *
     * @param string|null $module
     * @param int|string|null $id
     * @param array $params
     * @return void
     */
    private function handleResponse(?string $module, int|string|null $id, array $params): void
    {
        $api = new Api($this->response, $this->apiStructure);

        // Register all request handlers
        $api->registerHandler(new GetRequestHandler($this->schema))
            ->registerHandler(new PostRequestHandler($this->schema))
            ->registerHandler(new PutRequestHandler($this->schema))
            ->registerHandler(new DeleteRequestHandler($this->schema))
            ->registerHandler(new OptionsRequestHandler());

        $api->response($module, $id, $params);
    }
}

