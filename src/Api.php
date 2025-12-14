<?php

declare(strict_types=1);

namespace ApiGenerator;

use ApiGenerator\Contract\RequestHandlerInterface;
use ApiGenerator\Contract\ResponseInterface;
use ApiGenerator\Exception\InvalidRequestMethodException;
use ApiGenerator\Exception\ModuleNotFoundException;

/**
 * Class Api
 * Main API coordinator class
 *
 * @package ApiGenerator
 */
class Api
{
    public const AVAILABLE_REQUEST_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    private array $apiStructure;
    private string $requestMethod;
    private ResponseInterface $response;
    /** @var RequestHandlerInterface[] */
    private array $handlers = [];

    /**
     * Api constructor.
     *
     * @param ResponseInterface $response
     * @param array $apiStructure
     * @param string|null $requestMethod
     * @throws InvalidRequestMethodException
     */
    public function __construct(
        ResponseInterface $response,
        array $apiStructure = [],
        ?string $requestMethod = null
    ) {
        $this->response = $response;
        $this->apiStructure = $apiStructure;
        $this->requestMethod = $requestMethod ?? $_SERVER['REQUEST_METHOD'];

        if (!in_array($this->requestMethod, self::AVAILABLE_REQUEST_METHODS)) {
            throw new InvalidRequestMethodException(
                sprintf('Method "%s" is not available', $this->requestMethod)
            );
        }
    }

    /**
     * Register a request handler
     *
     * @param RequestHandlerInterface $handler
     * @return self
     */
    public function registerHandler(RequestHandlerInterface $handler): self
    {
        $this->handlers[] = $handler;
        return $this;
    }

    /**
     * Handle the request and send response
     *
     * @param string|null $module
     * @param int|string|null $id
     * @param array $params
     * @return void
     * @throws ModuleNotFoundException
     */
    public function response(?string $module, int|string|null $id, array $params): void
    {
        if ($module !== null && !isset($this->apiStructure[$module])) {
            throw new ModuleNotFoundException(
                sprintf('Module "%s" is not available in the API', $module)
            );
        }

        $this->response->sendHeaders();

        $data = $this->handleRequest($module, $id, $params);

        $this->response->send($data);
    }

    /**
     * Handle the request using appropriate handler
     *
     * @param string|null $module
     * @param int|string|null $id
     * @param array $params
     * @return array
     */
    private function handleRequest(?string $module, int|string|null $id, array $params): array
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($this->requestMethod)) {
                return $handler->handle($module, $id, $params);
            }
        }

        return [];
    }
}

