# Architecture Overview

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Client Request                           │
│                    (GET, POST, PUT, DELETE, etc.)                │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                         Generator                                │
│  Entry point: Coordinates API generation and request handling   │
│                                                                   │
│  + api(module, id, params)                                       │
│  + generate()                                                    │
│  + getApiStructure()                                             │
└─────────────┬───────────────────────────┬───────────────────────┘
              │                           │
              ▼                           ▼
┌─────────────────────────┐  ┌──────────────────────────────────┐
│  ApiStructureBuilder    │  │             Api                  │
│  Builds API structure   │  │  Coordinates request handling    │
│  from database schema   │  │                                  │
│                         │  │  + registerHandler()             │
│  + build()              │  │  + response()                    │
└────────┬────────────────┘  └──────┬────────────────┬──────────┘
         │                          │                │
         │                          │                │
         │                          ▼                ▼
         │              ┌────────────────┐  ┌──────────────────┐
         │              │ ResponseInterface│  │ RequestHandler   │
         │              │                  │  │  Interface       │
         │              └────────┬─────────┘  └────┬─────────────┘
         │                       │                 │
         ▼                       ▼                 ▼
┌─────────────────┐   ┌──────────────────┐  ┌───────────────────┐
│ SchemaInterface │   │  JsonResponse    │  │ GetRequestHandler │
└────────┬────────┘   │                  │  │ PostRequestHandler│
         │            │  + send()        │  │ PutRequestHandler │
         │            │  + sendHeaders() │  │ DeleteRequestHandler
         ▼            └──────────────────┘  │ OptionsRequestHandler
┌─────────────────┐                        └───────────────────┘
│     Schema      │
│                 │
│ Database ops:   │
│  + getTables()  │
│  + getResults() │
│  + insert()     │
│  + update()     │
│  + delete()     │
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│   Doctrine DBAL         │
│   Database Connection   │
└─────────────────────────┘
```

## Component Relationships

### Layer Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│                                                               │
│  ┌─────────────┐  ┌──────────────┐  ┌───────────────────┐  │
│  │  Generator  │  │     Api      │  │  JsonResponse     │  │
│  └─────────────┘  └──────────────┘  └───────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                      Business Layer                          │
│                                                               │
│  ┌─────────────────────┐  ┌───────────────────────────────┐ │
│  │ ApiStructureBuilder │  │  Request Handlers             │ │
│  │                     │  │  (Get, Post, Put, Delete)     │ │
│  └─────────────────────┘  └───────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                       Data Layer                             │
│                                                               │
│  ┌──────────────┐                                            │
│  │    Schema    │                                            │
│  └──────────────┘                                            │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    Database (Doctrine)                       │
└─────────────────────────────────────────────────────────────┘
```

## Request Flow

```
1. Client Request
   │
   ▼
2. Generator.api()
   │
   ├─→ Generate API structure (first time)
   │   │
   │   └─→ ApiStructureBuilder.build()
   │       │
   │       └─→ Schema.getTables() & getTableColumns()
   │
   └─→ Handle request
       │
       └─→ Api.response()
           │
           ├─→ Validate module exists
           │
           ├─→ Send CORS headers
           │   │
           │   └─→ ResponseInterface.sendHeaders()
           │
           ├─→ Find appropriate handler
           │   │
           │   └─→ Loop through registered handlers
           │       │
           │       └─→ RequestHandlerInterface.supports(method)
           │
           ├─→ Execute handler
           │   │
           │   └─→ RequestHandlerInterface.handle(module, id, params)
           │       │
           │       └─→ Schema operations (get, insert, update, delete)
           │
           └─→ Send response
               │
               └─→ ResponseInterface.send(data)
```

## Dependency Graph

```
Generator
  ├── depends on → SchemaInterface (injected)
  └── depends on → ResponseInterface (injected)

Api
  ├── depends on → ResponseInterface (injected)
  └── depends on → RequestHandlerInterface[] (registered)

ApiStructureBuilder
  └── depends on → SchemaInterface (injected)

Request Handlers (GetRequestHandler, PostRequestHandler, etc.)
  └── depends on → SchemaInterface (injected)

Schema
  └── depends on → Doctrine\DBAL\Connection (injected)

JsonResponse
  └── no dependencies
```

## SOLID Principles Visualization

### Single Responsibility Principle

```
┌──────────────────┐  One responsibility:
│     Schema       │  Database operations
└──────────────────┘

┌──────────────────┐  One responsibility:
│  JsonResponse    │  Response formatting
└──────────────────┘

┌──────────────────┐  One responsibility:
│ GetRequestHandler│  Handle GET requests
└──────────────────┘

┌──────────────────┐  One responsibility:
│PostRequestHandler│  Handle POST requests
└──────────────────┘
```

### Open/Closed Principle

```
Want to add new HTTP method?
┌─────────────────────────────────────────────┐
│ Create new handler implementing             │
│ RequestHandlerInterface                     │
│                                             │
│ class CustomHandler implements             │
│      RequestHandlerInterface                │
│                                             │
│ NO need to modify existing classes!        │
└─────────────────────────────────────────────┘

Want to change response format?
┌─────────────────────────────────────────────┐
│ Create new response implementing            │
│ ResponseInterface                           │
│                                             │
│ class XmlResponse implements                │
│      ResponseInterface                      │
│                                             │
│ NO need to modify existing classes!        │
└─────────────────────────────────────────────┘
```

### Liskov Substitution Principle

```
┌────────────────────┐
│ SchemaInterface    │
└─────────┬──────────┘
          │
    ┌─────┴─────┬──────────────┬───────────────┐
    ▼           ▼              ▼               ▼
┌────────┐ ┌─────────────┐ ┌────────────┐ ┌──────────┐
│ Schema │ │CachedSchema │ │LoggingSchema│ │MockSchema│
└────────┘ └─────────────┘ └────────────┘ └──────────┘

All are substitutable - any can be used wherever SchemaInterface is expected
```

### Interface Segregation Principle

```
Instead of one fat interface:
┌──────────────────────────────────────┐
│        ApiInterface                  │
│  (everything in one interface)       │
│                                      │
│  + getTables()                       │
│  + insert()                          │
│  + sendHeaders()                     │
│  + handleRequest()                   │
└──────────────────────────────────────┘

We have focused interfaces:
┌─────────────────┐  ┌──────────────────┐  ┌────────────────────┐
│SchemaInterface  │  │ResponseInterface │  │RequestHandler      │
│  + getTables()  │  │  + send()        │  │  Interface         │
│  + insert()     │  │  + sendHeaders() │  │  + handle()        │
│  + update()     │  │                  │  │  + supports()      │
└─────────────────┘  └──────────────────┘  └────────────────────┘
```

### Dependency Inversion Principle

```
High-level modules depend on abstractions:

┌──────────────┐
│  Generator   │ ──depends on──> ┌──────────────────┐
└──────────────┘                 │ SchemaInterface  │
                                 └──────────────────┘
                                          △
                                          │ implements
                                          │
                                    ┌──────────┐
                                    │  Schema  │
                                    └──────────┘

NOT this (bad):
┌──────────────┐
│  Generator   │ ──depends on──> ┌──────────┐
└──────────────┘                 │  Schema  │ (concrete)
                                 └──────────┘
```

## Extension Points

The architecture provides several extension points:

### 1. Custom Schema Implementations
```
SchemaInterface
  ├── Schema (database)
  ├── CachedSchema (with caching)
  ├── LoggingSchema (with logging)
  ├── MockSchema (for testing)
  └── Your custom implementation
```

### 2. Custom Response Formats
```
ResponseInterface
  ├── JsonResponse
  ├── XmlResponse
  ├── CsvResponse
  └── Your custom implementation
```

### 3. Custom Request Handlers
```
RequestHandlerInterface
  ├── GetRequestHandler
  ├── PostRequestHandler
  ├── PutRequestHandler
  ├── DeleteRequestHandler
  ├── OptionsRequestHandler
  ├── BatchRequestHandler
  ├── ValidationHandler (middleware)
  └── Your custom implementation
```

## Decorator Pattern Example

```
                      ┌─────────────────┐
                      │ SchemaInterface │
                      └────────┬────────┘
                               │
                    ┌──────────┴──────────┐
                    │                     │
              ┌──────────┐         ┌──────────────┐
              │  Schema  │         │ SchemaDecorator│
              │ (base)   │         └────────┬───────┘
              └──────────┘                  │
                                   ┌────────┴────────┐
                                   │                 │
                            ┌──────────────┐  ┌─────────────┐
                            │CachedSchema  │  │LoggingSchema│
                            └──────────────┘  └─────────────┘

Usage:
$schema = new LoggingSchema(
    new CachedSchema(
        new Schema($conn),
        $cache
    ),
    $logger
);

Layers: Logging → Caching → Database
```

## Thread Safety

The architecture is designed to be thread-safe:

1. **Stateless Handlers**: Request handlers are stateless and can be reused
2. **Immutable Interfaces**: Interfaces define clear contracts
3. **No Global State**: All dependencies are injected
4. **Connection Pooling**: Doctrine handles connection management

## Performance Considerations

```
Request Path:
1. Generator.api()           ~ 0.1ms  (routing)
2. Api.response()            ~ 0.1ms  (coordination)
3. Handler.handle()          ~ 0.1ms  (logic)
4. Schema.operation()        ~ 5-50ms (database)
5. Response.send()           ~ 0.1ms  (formatting)

Total: ~5-50ms (database is bottleneck)

Optimization strategies:
- Add CachedSchema decorator
- Use connection pooling
- Add query optimization
- Use lazy loading
```

## Scalability

The architecture supports scaling:

1. **Horizontal Scaling**: Stateless design allows multiple instances
2. **Caching**: CachedSchema reduces database load
3. **Database Pooling**: Multiple connections via Doctrine
4. **CDN Integration**: Static assets can be served via CDN
5. **Load Balancing**: Multiple API instances behind load balancer

```
           ┌──────────────┐
           │ Load Balancer│
           └──────┬───────┘
                  │
      ┌───────────┼───────────┐
      │           │           │
      ▼           ▼           ▼
┌──────────┐ ┌──────────┐ ┌──────────┐
│API Server│ │API Server│ │API Server│
│Instance 1│ │Instance 2│ │Instance 3│
└────┬─────┘ └────┬─────┘ └────┬─────┘
     │            │            │
     └────────────┼────────────┘
                  │
                  ▼
          ┌───────────────┐
          │   Database    │
          │   (Master)    │
          └───────┬───────┘
                  │
          ┌───────┴───────┐
          │               │
          ▼               ▼
    ┌──────────┐    ┌──────────┐
    │ Read     │    │ Read     │
    │ Replica 1│    │ Replica 2│
    └──────────┘    └──────────┘
```

## Summary

The architecture follows clean architecture principles:

✅ **Separation of Concerns**: Clear boundaries between layers  
✅ **Dependency Inversion**: High-level depends on abstractions  
✅ **Testability**: Each component can be tested independently  
✅ **Extensibility**: Easy to add new features without modification  
✅ **Maintainability**: Small, focused classes with clear responsibilities  
✅ **Scalability**: Stateless design supports horizontal scaling  

This makes the API Generator production-ready, maintainable, and extensible!

