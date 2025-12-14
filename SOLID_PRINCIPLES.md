# SOLID Principles Implementation

This document explains how the API Generator implements SOLID principles.

## Overview

SOLID is an acronym for five design principles intended to make software designs more understandable, flexible, and maintainable:

- **S**ingle Responsibility Principle
- **O**pen/Closed Principle
- **L**iskov Substitution Principle
- **I**nterface Segregation Principle
- **D**ependency Inversion Principle

## 1. Single Responsibility Principle (SRP)

> A class should have one, and only one, reason to change.

### Implementation

Each class in the refactored codebase has a single, well-defined responsibility:

#### `Schema` Class
**Responsibility**: Database operations only
```php
// Only handles database queries and schema operations
class Schema implements SchemaInterface
{
    public function getTables(): array { }
    public function getTableColumns(string $table): array { }
    public function getResults(string $module): array { }
    public function insert(string $module, array $params): mixed { }
    // ... other database operations
}
```

#### `JsonResponse` Class
**Responsibility**: HTTP response formatting and headers
```php
// Only handles response formatting and headers
class JsonResponse implements ResponseInterface
{
    public function send(array $data): void { }
    public function sendHeaders(): void { }
}
```

#### `ApiStructureBuilder` Class
**Responsibility**: Building API structure from schema
```php
// Only handles API structure generation
class ApiStructureBuilder
{
    public function build(): array { }
    private function createApiStructure(array $tables): array { }
}
```

#### Request Handler Classes
**Responsibility**: Each handler processes one HTTP method
```php
// GetRequestHandler only handles GET requests
class GetRequestHandler implements RequestHandlerInterface
{
    public function handle(?string $module, $id, array $params): array { }
    public function supports(string $method): bool { }
}
```

### Before (Violating SRP):
The old `Api` class had multiple responsibilities:
- Request routing
- Header management
- Response formatting
- Business logic

```php
class Api
{
    public function response($module, $id, $params) {
        // Routing logic
        switch ($this->requestMethod) { }
        
        // Database operations
        $this->schema->update($module, $id, $params);
        
        // Header management
        $this->sendGeneralHeaders();
        $this->sendOptionHeaders();
        
        // Response formatting
        $this->sendJsonResponse($data);
    }
}
```

## 2. Open/Closed Principle (OCP)

> Software entities should be open for extension, but closed for modification.

### Implementation

#### Strategy Pattern for Request Handlers
New HTTP methods can be added without modifying existing code:

```php
// Add a new handler without modifying Api class
class CustomRequestHandler implements RequestHandlerInterface
{
    public function handle(?string $module, $id, array $params): array
    {
        // Custom logic
    }
    
    public function supports(string $method): bool
    {
        return $method === 'CUSTOM_METHOD';
    }
}

// Register it
$api->registerHandler(new CustomRequestHandler());
```

#### Handler Registry
The `Api` class accepts handlers via registration:

```php
class Api
{
    private array $handlers = [];
    
    public function registerHandler(RequestHandlerInterface $handler): self
    {
        $this->handlers[] = $handler;
        return $this;
    }
    
    private function handleRequest($module, $id, $params): array
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($this->requestMethod)) {
                return $handler->handle($module, $id, $params);
            }
        }
        return [];
    }
}
```

### Before (Violating OCP):
Adding a new HTTP method required modifying the switch statement:

```php
switch ($this->requestMethod) {
    case 'GET': /* ... */ break;
    case 'POST': /* ... */ break;
    // Adding new method = modifying this code
}
```

## 3. Liskov Substitution Principle (LSP)

> Objects of a superclass should be replaceable with objects of a subclass without breaking the application.

### Implementation

All implementations can be substituted for their interfaces without side effects:

#### Schema Substitution
```php
interface SchemaInterface
{
    public function getResults(string $module): array;
    public function insert(string $module, array $params): mixed;
    // ...
}

// Original implementation
$schema = new Schema($conn);

// Can be replaced with a mock or alternative implementation
class CachedSchema implements SchemaInterface
{
    private SchemaInterface $schema;
    private CacheInterface $cache;
    
    public function getResults(string $module): array
    {
        if ($this->cache->has($module)) {
            return $this->cache->get($module);
        }
        return $this->schema->getResults($module);
    }
    // ... implements all methods with same contracts
}

$schema = new CachedSchema(new Schema($conn), $cache);
// Works exactly the same way, no code changes needed
```

#### Response Substitution
```php
// JSON response
$response = new JsonResponse();

// Can be replaced with XML, CSV, or any other format
class XmlResponse implements ResponseInterface
{
    public function send(array $data): void
    {
        header('Content-Type: application/xml');
        echo $this->arrayToXml($data);
    }
    
    public function sendHeaders(): void
    {
        // Same CORS headers
    }
}

$response = new XmlResponse();
// Generator works with any ResponseInterface implementation
```

#### Handler Substitution
```php
// All handlers are interchangeable
$handlers = [
    new GetRequestHandler($schema),
    new PostRequestHandler($schema),
    new CustomHandler($schema, $logger),
];

// Each follows the same contract
foreach ($handlers as $handler) {
    if ($handler->supports($method)) {
        return $handler->handle($module, $id, $params);
    }
}
```

## 4. Interface Segregation Principle (ISP)

> A client should not be forced to depend on methods it does not use.

### Implementation

We use focused interfaces for specific concerns:

#### SchemaInterface
Only schema-related operations:
```php
interface SchemaInterface
{
    public function getTables(): array;
    public function getTableColumns(string $table): array;
    public function getResults(string $module): array;
    public function getResult(string $module, $id): array;
    public function insert(string $module, array $params): mixed;
    public function update(string $module, $id, array $params): int;
    public function delete(string $module, $id): mixed;
}
```

#### ResponseInterface
Only response-related operations:
```php
interface ResponseInterface
{
    public function send(array $data): void;
    public function sendHeaders(): void;
}
```

#### RequestHandlerInterface
Only request handling operations:
```php
interface RequestHandlerInterface
{
    public function handle(?string $module, $id, array $params): array;
    public function supports(string $method): bool;
}
```

### Before (Violating ISP):
If we had one large interface:

```php
// BAD: Fat interface
interface ApiInterface
{
    // Schema operations
    public function getTables(): array;
    public function insert($module, $params);
    
    // Response operations
    public function sendHeaders(): void;
    public function sendJson($data): void;
    
    // Request operations
    public function handleGet($module): array;
    public function handlePost($module, $params): array;
}

// Classes forced to implement everything, even if not needed
class ReadOnlyApi implements ApiInterface
{
    public function insert($module, $params) {
        throw new Exception('Not supported'); // Forced to implement
    }
    // ...
}
```

## 5. Dependency Inversion Principle (DIP)

> Depend upon abstractions, not concretions.

### Implementation

High-level modules depend on abstractions (interfaces), not concrete implementations:

#### Generator Class
Depends on interfaces, not concrete classes:
```php
class Generator
{
    // Depends on interface, not concrete Schema class
    private SchemaInterface $schema;
    
    // Depends on interface, not concrete JsonResponse class
    private ResponseInterface $response;
    
    public function __construct(
        SchemaInterface $schema,      // Abstraction
        ResponseInterface $response    // Abstraction
    ) {
        $this->schema = $schema;
        $this->response = $response;
    }
}
```

#### Api Class
Depends on interfaces:
```php
class Api
{
    private ResponseInterface $response;
    private array $handlers = []; // Array of RequestHandlerInterface
    
    public function __construct(ResponseInterface $response, array $apiStructure = [])
    {
        $this->response = $response; // Depends on abstraction
    }
    
    public function registerHandler(RequestHandlerInterface $handler): self
    {
        $this->handlers[] = $handler; // Depends on abstraction
        return $this;
    }
}
```

#### Request Handlers
Depend on SchemaInterface:
```php
class GetRequestHandler implements RequestHandlerInterface
{
    private SchemaInterface $schema; // Abstraction, not concrete Schema
    
    public function __construct(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }
}
```

### Benefits

1. **Easy Testing**: Mock interfaces instead of concrete classes
```php
// Create a mock schema for testing
class MockSchema implements SchemaInterface
{
    public function getResults(string $module): array
    {
        return ['test' => 'data'];
    }
    // ... implement other methods
}

// Test without database
$mockSchema = new MockSchema();
$generator = new Generator($mockSchema, new JsonResponse());
```

2. **Easy Extension**: Swap implementations without changing code
```php
// Use cached version
$schema = new CachedSchema(new Schema($conn), $cache);

// Use logging version
$schema = new LoggingSchema(new Schema($conn), $logger);

// Same Generator code works with all
$generator = new Generator($schema, $response);
```

3. **Decoupling**: Classes don't know about concrete implementations
```php
// Generator doesn't know about JsonResponse, XmlResponse, etc.
// It only knows about ResponseInterface
$generator = new Generator($schema, $response); // Any ResponseInterface
```

### Before (Violating DIP):
```php
class Generator
{
    private Schema $schema; // Depends on concrete class
    
    public function __construct($conn)
    {
        // Creates concrete Schema directly
        $this->schema = new Schema($conn);
    }
}

class Api
{
    private Schema $schema; // Depends on concrete class
    
    public function response($module, $id, $params)
    {
        // Hardcoded to use json_encode
        echo json_encode($data);
    }
}
```

## Conclusion

The refactored API Generator follows all SOLID principles:

- ✅ **SRP**: Each class has one responsibility
- ✅ **OCP**: Extensible without modification via handler registration
- ✅ **LSP**: Implementations are substitutable via interfaces
- ✅ **ISP**: Focused, segregated interfaces
- ✅ **DIP**: Depends on abstractions, not concretions

This makes the codebase:
- **Maintainable**: Easier to understand and modify
- **Testable**: Each component can be tested in isolation
- **Extensible**: New features can be added without breaking existing code
- **Flexible**: Components can be swapped easily
- **Robust**: Type safety and clear contracts reduce bugs

