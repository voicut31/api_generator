# Usage Examples

This document provides practical examples of using the refactored API Generator.

## Basic Setup

### 1. Install Dependencies

```bash
composer install
composer dump-autoload
```

### 2. Basic Usage

```php
<?php

require 'vendor/autoload.php';

use ApiGenerator\Generator;
use ApiGenerator\Service\Schema;
use ApiGenerator\Http\JsonResponse;
use Doctrine\DBAL\DriverManager;

// Create database connection
$connectionParams = [
    'dbname' => 'mydb',
    'user' => 'user',
    'password' => 'secret',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
];
$conn = DriverManager::getConnection($connectionParams);

// Initialize components
$schema = new Schema($conn);
$response = new JsonResponse();
$generator = new Generator($schema, $response);

// Handle API request
$module = $_GET['module'] ?? null;  // e.g., 'users'
$id = $_GET['id'] ?? null;           // e.g., 123
$params = $_POST ?? [];              // POST data

$generator->api($module, $id, $params);
```

## Advanced Examples

### 3. Custom Response Format (XML)

```php
<?php

use ApiGenerator\Contract\ResponseInterface;
use ApiGenerator\Generator;
use ApiGenerator\Service\Schema;

class XmlResponse implements ResponseInterface
{
    public function send(array $data): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        echo $this->arrayToXml($data);
    }

    public function sendHeaders(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: X-Requested-With, content-type, authorization');
    }

    private function arrayToXml(array $data, string $rootElement = 'response'): string
    {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><{$rootElement}></{$rootElement}>");
        $this->arrayToXmlRecursive($data, $xml);
        return $xml->asXML();
    }

    private function arrayToXmlRecursive(array $data, SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild((string)$key);
                $this->arrayToXmlRecursive($value, $subnode);
            } else {
                $xml->addChild((string)$key, htmlspecialchars((string)$value));
            }
        }
    }
}

// Usage
$schema = new Schema($conn);
$response = new XmlResponse();
$generator = new Generator($schema, $response);
$generator->api($module, $id, $params);
```

### 4. Cached Schema (Performance Optimization)

```php
<?php

use ApiGenerator\Contract\SchemaInterface;
use ApiGenerator\Service\Schema;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;

class CachedSchema implements SchemaInterface
{
    private SchemaInterface $schema;
    private array $cache = [];
    private int $ttl = 3600; // 1 hour

    public function __construct(SchemaInterface $schema, int $ttl = 3600)
    {
        $this->schema = $schema;
        $this->ttl = $ttl;
    }

    public function getTables(): array
    {
        return $this->getCached('tables', fn() => $this->schema->getTables());
    }

    public function getTableColumns(string $table): array
    {
        return $this->getCached("columns_{$table}", fn() => $this->schema->getTableColumns($table));
    }

    public function getResults(string $module): array
    {
        return $this->getCached("results_{$module}", fn() => $this->schema->getResults($module));
    }

    public function getResult(string $module, int|string $id): array
    {
        return $this->getCached("result_{$module}_{$id}", fn() => $this->schema->getResult($module, $id));
    }

    public function insert(string $module, array $params): mixed
    {
        $this->clearCache("results_{$module}");
        return $this->schema->insert($module, $params);
    }

    public function update(string $module, int|string $id, array $params): int
    {
        $this->clearCache("results_{$module}");
        $this->clearCache("result_{$module}_{$id}");
        return $this->schema->update($module, $id, $params);
    }

    public function delete(string $module, int|string $id): mixed
    {
        $this->clearCache("results_{$module}");
        $this->clearCache("result_{$module}_{$id}");
        return $this->schema->delete($module, $id);
    }

    private function getCached(string $key, callable $callback): mixed
    {
        if (isset($this->cache[$key]) && $this->cache[$key]['expires'] > time()) {
            return $this->cache[$key]['data'];
        }

        $data = $callback();
        $this->cache[$key] = [
            'data' => $data,
            'expires' => time() + $this->ttl,
        ];

        return $data;
    }

    private function clearCache(string $key): void
    {
        unset($this->cache[$key]);
    }
}

// Usage
$baseSchema = new Schema($conn);
$cachedSchema = new CachedSchema($baseSchema, 3600); // Cache for 1 hour
$generator = new Generator($cachedSchema, new JsonResponse());
$generator->api($module, $id, $params);
```

### 5. Logging Schema (Debugging)

```php
<?php

use ApiGenerator\Contract\SchemaInterface;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Schema\Column;

class LoggingSchema implements SchemaInterface
{
    private SchemaInterface $schema;
    private LoggerInterface $logger;

    public function __construct(SchemaInterface $schema, LoggerInterface $logger)
    {
        $this->schema = $schema;
        $this->logger = $logger;
    }

    public function getTables(): array
    {
        $this->logger->info('Getting tables');
        $result = $this->schema->getTables();
        $this->logger->info('Found tables', ['count' => count($result)]);
        return $result;
    }

    public function getTableColumns(string $table): array
    {
        $this->logger->info('Getting columns', ['table' => $table]);
        return $this->schema->getTableColumns($table);
    }

    public function getResults(string $module): array
    {
        $this->logger->info('Getting results', ['module' => $module]);
        $startTime = microtime(true);
        $result = $this->schema->getResults($module);
        $duration = microtime(true) - $startTime;
        $this->logger->info('Got results', [
            'module' => $module,
            'count' => count($result),
            'duration' => $duration,
        ]);
        return $result;
    }

    public function getResult(string $module, int|string $id): array
    {
        $this->logger->info('Getting result', ['module' => $module, 'id' => $id]);
        return $this->schema->getResult($module, $id);
    }

    public function insert(string $module, array $params): mixed
    {
        $this->logger->info('Inserting record', ['module' => $module, 'params' => $params]);
        $result = $this->schema->insert($module, $params);
        $this->logger->info('Inserted record', ['module' => $module, 'result' => $result]);
        return $result;
    }

    public function update(string $module, int|string $id, array $params): int
    {
        $this->logger->info('Updating record', ['module' => $module, 'id' => $id, 'params' => $params]);
        $result = $this->schema->update($module, $id, $params);
        $this->logger->info('Updated record', ['module' => $module, 'id' => $id, 'affected' => $result]);
        return $result;
    }

    public function delete(string $module, int|string $id): mixed
    {
        $this->logger->info('Deleting record', ['module' => $module, 'id' => $id]);
        $result = $this->schema->delete($module, $id);
        $this->logger->info('Deleted record', ['module' => $module, 'id' => $id]);
        return $result;
    }
}

// Usage
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('api');
$logger->pushHandler(new StreamHandler('api.log', Logger::INFO));

$baseSchema = new Schema($conn);
$loggingSchema = new LoggingSchema($baseSchema, $logger);
$generator = new Generator($loggingSchema, new JsonResponse());
$generator->api($module, $id, $params);
```

### 6. Custom Request Handler (Batch Operations)

```php
<?php

use ApiGenerator\Contract\RequestHandlerInterface;
use ApiGenerator\Contract\SchemaInterface;
use ApiGenerator\Api;
use ApiGenerator\Http\JsonResponse;

class BatchRequestHandler implements RequestHandlerInterface
{
    private SchemaInterface $schema;

    public function __construct(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    public function handle(?string $module, int|string|null $id, array $params): array
    {
        if (!isset($params['operations']) || !is_array($params['operations'])) {
            return ['error' => 'operations array required'];
        }

        $results = [];
        foreach ($params['operations'] as $operation) {
            try {
                $results[] = $this->handleOperation($module, $operation);
            } catch (Exception $e) {
                $results[] = ['error' => $e->getMessage()];
            }
        }

        return ['results' => $results];
    }

    public function supports(string $method): bool
    {
        return $method === 'BATCH';
    }

    private function handleOperation(string $module, array $operation): array
    {
        $action = $operation['action'] ?? null;
        $id = $operation['id'] ?? null;
        $params = $operation['params'] ?? [];

        return match ($action) {
            'insert' => ['inserted' => $this->schema->insert($module, $params)],
            'update' => ['updated' => $this->schema->update($module, $id, $params)],
            'delete' => ['deleted' => $this->schema->delete($module, $id)],
            default => ['error' => 'Invalid action'],
        };
    }
}

// Usage
$api = new Api(new JsonResponse(), $apiStructure, 'BATCH');
$api->registerHandler(new BatchRequestHandler($schema));

$params = [
    'operations' => [
        ['action' => 'insert', 'params' => ['name' => 'John']],
        ['action' => 'update', 'id' => 1, 'params' => ['name' => 'Jane']],
        ['action' => 'delete', 'id' => 2],
    ]
];

$api->response($module, null, $params);
```

### 7. Validation Handler (Middleware Pattern)

```php
<?php

use ApiGenerator\Contract\RequestHandlerInterface;

class ValidationHandler implements RequestHandlerInterface
{
    private RequestHandlerInterface $handler;
    private array $rules;

    public function __construct(RequestHandlerInterface $handler, array $rules)
    {
        $this->handler = $handler;
        $this->rules = $rules;
    }

    public function handle(?string $module, int|string|null $id, array $params): array
    {
        // Validate params
        $errors = $this->validate($module, $params);
        
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        // Pass to actual handler
        return $this->handler->handle($module, $id, $params);
    }

    public function supports(string $method): bool
    {
        return $this->handler->supports($method);
    }

    private function validate(string $module, array $params): array
    {
        $errors = [];
        $moduleRules = $this->rules[$module] ?? [];

        foreach ($moduleRules as $field => $rules) {
            if (isset($rules['required']) && $rules['required'] && !isset($params[$field])) {
                $errors[$field] = "Field {$field} is required";
            }

            if (isset($params[$field]) && isset($rules['type'])) {
                $type = gettype($params[$field]);
                if ($type !== $rules['type']) {
                    $errors[$field] = "Field {$field} must be of type {$rules['type']}";
                }
            }

            if (isset($params[$field]) && isset($rules['min_length'])) {
                if (strlen($params[$field]) < $rules['min_length']) {
                    $errors[$field] = "Field {$field} must be at least {$rules['min_length']} characters";
                }
            }
        }

        return $errors;
    }
}

// Usage
use ApiGenerator\Handler\PostRequestHandler;

$rules = [
    'users' => [
        'name' => ['required' => true, 'type' => 'string', 'min_length' => 3],
        'email' => ['required' => true, 'type' => 'string'],
        'age' => ['required' => false, 'type' => 'integer'],
    ],
];

$postHandler = new PostRequestHandler($schema);
$validatedPostHandler = new ValidationHandler($postHandler, $rules);

$api->registerHandler($validatedPostHandler);
```

### 8. Error Handling

```php
<?php

use ApiGenerator\Exception\InvalidRequestMethodException;
use ApiGenerator\Exception\ModuleNotFoundException;

try {
    $generator->api($module, $id, $params);
} catch (ModuleNotFoundException $e) {
    http_response_code(404);
    echo json_encode(['error' => 'Module not found', 'message' => $e->getMessage()]);
} catch (InvalidRequestMethodException $e) {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}
```

### 9. Complete Example with All Features

```php
<?php

require 'vendor/autoload.php';

use ApiGenerator\Generator;
use ApiGenerator\Service\Schema;
use ApiGenerator\Http\JsonResponse;
use Doctrine\DBAL\DriverManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Database connection
$conn = DriverManager::getConnection([
    'dbname' => 'mydb',
    'user' => 'user',
    'password' => 'secret',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
]);

// Setup logger
$logger = new Logger('api');
$logger->pushHandler(new StreamHandler(__DIR__ . '/logs/api.log', Logger::INFO));

// Create decorated schema (caching + logging)
$baseSchema = new Schema($conn);
$cachedSchema = new CachedSchema($baseSchema, 3600);
$loggingSchema = new LoggingSchema($cachedSchema, $logger);

// Create response
$response = new JsonResponse();

// Create generator
$generator = new Generator($loggingSchema, $response);

// Parse request
$module = $_GET['module'] ?? null;
$id = $_GET['id'] ?? null;
$params = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = json_decode(file_get_contents('php://input'), true) ?? [];
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    parse_str(file_get_contents('php://input'), $params);
}

// Handle request with error handling
try {
    $generator->api($module, $id, $params);
} catch (ModuleNotFoundException $e) {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found', 'message' => $e->getMessage()]);
    $logger->error('Module not found', ['module' => $module]);
} catch (InvalidRequestMethodException $e) {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed', 'message' => $e->getMessage()]);
    $logger->error('Invalid method', ['method' => $_SERVER['REQUEST_METHOD']]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'message' => 'An error occurred']);
    $logger->error('Internal error', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
```

## Testing Examples

### 10. Unit Testing

```php
<?php

use PHPUnit\Framework\TestCase;
use ApiGenerator\Handler\GetRequestHandler;
use ApiGenerator\Contract\SchemaInterface;

class GetRequestHandlerTest extends TestCase
{
    public function testHandleWithId(): void
    {
        $schema = $this->createMock(SchemaInterface::class);
        $schema->expects($this->once())
            ->method('getResult')
            ->with('users', 123)
            ->willReturn(['id' => 123, 'name' => 'John']);

        $handler = new GetRequestHandler($schema);
        $result = $handler->handle('users', 123, []);

        $this->assertEquals(['id' => 123, 'name' => 'John'], $result);
    }

    public function testHandleWithoutId(): void
    {
        $schema = $this->createMock(SchemaInterface::class);
        $schema->expects($this->once())
            ->method('getResults')
            ->with('users')
            ->willReturn([
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane'],
            ]);

        $handler = new GetRequestHandler($schema);
        $result = $handler->handle('users', null, []);

        $this->assertCount(2, $result);
    }

    public function testSupports(): void
    {
        $schema = $this->createMock(SchemaInterface::class);
        $handler = new GetRequestHandler($schema);

        $this->assertTrue($handler->supports('GET'));
        $this->assertFalse($handler->supports('POST'));
    }
}
```

## Conclusion

These examples demonstrate:
- Basic setup and usage
- Custom response formats
- Performance optimization with caching
- Debugging with logging
- Custom request handlers
- Validation middleware
- Error handling
- Complete production-ready setup
- Unit testing

The refactored architecture makes all of these patterns easy to implement and maintain!

