# Migration Guide - API Generator v0.2.0 to v0.3.0

## Overview

The API Generator has been refactored to follow SOLID principles and modern PHP standards. This guide will help you migrate from the old structure to the new one.

## Major Changes

### 1. Namespace Structure (PSR-4)
- **Old**: PSR-0 autoloading with `ApiGenerator` namespace
- **New**: PSR-4 autoloading with organized namespaces

### 2. Class Organization

#### Before:
```php
use ApiGenerator\Generator;
use ApiGenerator\Schema;
use ApiGenerator\Api;

$schema = new Schema($conn);
$generator = new Generator($conn);
$generator->api($module, $id, $params);
```

#### After:
```php
use ApiGenerator\Generator;
use ApiGenerator\Service\Schema;
use ApiGenerator\Http\JsonResponse;

$schema = new Schema($conn);
$response = new JsonResponse();
$generator = new Generator($schema, $response);
$generator->api($module, $id, $params);
```

## New Structure

### Namespace Organization

```
ApiGenerator\
├── Api.php                          # Main API coordinator
├── Generator.php                    # Entry point class
├── Contract\                        # Interfaces
│   ├── SchemaInterface.php
│   ├── ResponseInterface.php
│   └── RequestHandlerInterface.php
├── Service\                         # Business logic services
│   ├── Schema.php
│   └── ApiStructureBuilder.php
├── Handler\                         # Request handlers (Strategy pattern)
│   ├── GetRequestHandler.php
│   ├── PostRequestHandler.php
│   ├── PutRequestHandler.php
│   ├── DeleteRequestHandler.php
│   └── OptionsRequestHandler.php
├── Http\                           # HTTP layer
│   └── JsonResponse.php
└── Exception\                      # Custom exceptions
    ├── InvalidRequestMethodException.php
    └── ModuleNotFoundException.php
```

## SOLID Principles Applied

### 1. Single Responsibility Principle (SRP)
Each class now has a single, well-defined responsibility:
- `Schema`: Database operations only
- `JsonResponse`: Response formatting and headers
- `Api`: Request coordination
- `Generator`: API generation and bootstrapping
- Request Handlers: Each HTTP method has its own handler

### 2. Open/Closed Principle (OCP)
- New request handlers can be added without modifying existing code
- The `Api` class uses a handler registry system
- Custom handlers can be registered via `registerHandler()`

### 3. Liskov Substitution Principle (LSP)
- All handlers implement `RequestHandlerInterface`
- Schema implements `SchemaInterface`
- Response implements `ResponseInterface`
- Any implementation can be substituted without breaking functionality

### 4. Interface Segregation Principle (ISP)
- Focused interfaces for specific concerns
- `SchemaInterface`: Database operations
- `ResponseInterface`: Response handling
- `RequestHandlerInterface`: Request processing

### 5. Dependency Inversion Principle (DIP)
- Classes depend on abstractions (interfaces), not concrete implementations
- Constructor injection for dependencies
- Easy to test and extend

## Migration Steps

### Step 1: Update Composer

Run:
```bash
composer dump-autoload
```

### Step 2: Update Your Code

#### Old Code:
```php
use ApiGenerator\Generator;

$generator = new Generator($conn);
$generator->api('users', 1, []);
```

#### New Code:
```php
use ApiGenerator\Generator;
use ApiGenerator\Service\Schema;
use ApiGenerator\Http\JsonResponse;

$schema = new Schema($conn);
$response = new JsonResponse();
$generator = new Generator($schema, $response);
$generator->api('users', 1, []);
```

### Step 3: Update Exception Handling

#### Old:
```php
use Error;

try {
    $generator->api($module, $id, $params);
} catch (Error $e) {
    // Handle error
}
```

#### New:
```php
use ApiGenerator\Exception\InvalidRequestMethodException;
use ApiGenerator\Exception\ModuleNotFoundException;

try {
    $generator->api($module, $id, $params);
} catch (InvalidRequestMethodException $e) {
    // Handle invalid method
} catch (ModuleNotFoundException $e) {
    // Handle missing module
}
```

## Benefits of the New Structure

1. **Better Testability**: Each component can be tested in isolation
2. **Easier Maintenance**: Smaller, focused classes are easier to understand and modify
3. **Extensibility**: Add new features without modifying existing code
4. **Type Safety**: Strict typing throughout the codebase
5. **Modern PHP**: Uses PHP 8+ features (typed properties, union types, etc.)
6. **PSR-4 Compliance**: Standard autoloading for better compatibility

## Custom Implementations

### Custom Response Handler

```php
use ApiGenerator\Contract\ResponseInterface;

class XmlResponse implements ResponseInterface
{
    public function send(array $data): void
    {
        header('Content-Type: application/xml');
        // XML conversion logic
    }

    public function sendHeaders(): void
    {
        // Custom headers
    }
}

// Usage
$response = new XmlResponse();
$generator = new Generator($schema, $response);
```

### Custom Request Handler

```php
use ApiGenerator\Contract\RequestHandlerInterface;

class CustomHandler implements RequestHandlerInterface
{
    public function handle(?string $module, int|string|null $id, array $params): array
    {
        // Custom logic
        return ['custom' => 'response'];
    }

    public function supports(string $method): bool
    {
        return $method === 'CUSTOM';
    }
}

// Usage
$api = new Api($response, $apiStructure);
$api->registerHandler(new CustomHandler());
```

## Breaking Changes

1. **Constructor signatures changed**: `Generator` now requires `SchemaInterface` and `ResponseInterface`
2. **Exception types changed**: `Error` replaced with specific exceptions
3. **Namespace structure**: All classes moved to appropriate sub-namespaces
4. **Type hints**: Strict typing enforced throughout

## Support

If you encounter any issues during migration, please check:
1. PHP version (requires PHP 8.0+)
2. Composer autoload is regenerated
3. All type hints match your usage

For questions or issues, please open an issue on the project repository.

