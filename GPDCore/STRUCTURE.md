# GPDCore - Estructura de Carpetas

Este documento describe la organización del código en el directorio `GPDCore/src/`.

## Estructura Reorganizada

A partir de la versión actual, todos los archivos que anteriormente estaban en `Library/` han sido reorganizados en carpetas temáticas para mejorar la mantenibilidad y claridad del código.

### 📋 Contracts/ (15 archivos)
**Namespace:** `GPDCore\Contracts`

Contiene todas las interfaces del sistema que definen contratos entre componentes:

- **AppConfigInterface.php** - Contrato para configuración de la aplicación
- **AppContextInterface.php** - Contrato para el contexto de la aplicación
- **AppControllerInterface.php** - Contrato para controladores
- **ConfigProviderInterface.php** - Proveedores de configuración
- **IErrorManager.php** - Gestión de errores
- **IGQLException.php** - Excepciones GraphQL
- **MiddlewareProviderInterface.php** - Proveedores de middleware
- **MiddlewareQueueInterface.php** - Cola de middlewares
- **ResolverMiddlewareInterface.php** - Contrato para middlewares de resolvers GraphQL
- **ModuleProviderInterface.php** - Proveedores de módulos
- **ResolverManagerInterface.php** - Gestión de resolvers
- **ResolverProviderInterface.php** - Proveedores de resolvers
- **SchemaProviderInterface.php** - Proveedores de schemas
- **ServiceProviderInterface.php** - Proveedores de servicios
- **TypesProviderInterface.php** - Proveedores de tipos GraphQL

### 🏗️ Core/ (9 archivos)
**Namespace:** `GPDCore\Core`

Contiene las clases principales del framework y la lógica central:

- **AbstractModule.php** - Clase base para módulos de la aplicación
- **AppConfig.php** - Implementación de configuración de la aplicación
- **AppContext.php** - Contexto de ejecución de la aplicación
- **Application.php** - Clase principal de la aplicación
- **FrameworkHandler.php** - Manejador principal del framework
- **MiddlewareQueue.php** - Implementación de cola de middlewares
- **ResolverManager.php** - Gestor de resolvers GraphQL
- **SchemaManager.php** - Gestor de schemas GraphQL
- **TypesManager.php** - Gestor de tipos GraphQL

### 🔷 Graphql/ (9 archivos)
**Namespace:** `GPDCore\Graphql`

Componentes específicos para GraphQL:

- **AbstractCustomTypeFactory.php** - Fábrica base para tipos personalizados
- **RelayConnectionBuilder.php** - Constructor de conexiones paginadas siguiendo estándar Relay de GraphQL
- **ConnectionTypeFactory.php** - Fábrica para tipos de conexión
- **ArrayFieldResolverFactory.php** - Factory para crear resolvers de campos sobre arrays
- **DefaultDoctrineFieldResolver.php** - Resolver por defecto para campos Doctrine
- **FieldResolveFactory.php** - (Deprecado: Fusionado con ResolverFactory.php)
- **GraphqlSchemaUtilities.php** - Utilidades para schemas GraphQL
- **MiddlewareCallable.php** - Middleware callable reutilizable para resolvers
- **ResolverFactory.php** - Fábrica para resolvers
- **ResolverMiddleware.php** - Middleware para envolver resolvers con lógica adicional

### � DataLoaders/ (2 archivos)
**Namespace:** `GPDCore\DataLoaders`

Implementación del patrón DataLoader de GraphQL para prevención N+1:

- **EntityDataLoader.php** - DataLoader para entidades (previene consultas N+1)
- **CollectionDataLoader.php** - DataLoader para colecciones relacionadas

### 💾 Doctrine/ (6 archivos)
**Namespace:** `GPDCore\Doctrine`

Utilidades y componentes relacionados con Doctrine ORM:

- **EntityHydrator.php** - Hydrator para poblar entidades con datos de arrays
- **DoctrineSQLLogger.php** - Logger para consultas SQL
- **EntityAssociation.php** - Gestión de asociaciones de entidades
- **EntityMetadataHelper.php** - Helper para metadata e información de entidades
- **QueryBuilderHelper.php** - Helper para QueryBuilder y manejo de asociaciones de Doctrine
- **QueryModifier.php** - Modificador callable para personalizar queries

### 🛣️ Routing/ (3 archivos)
**Namespace:** `GPDCore\Routing`

Componentes de enrutamiento y controladores:

- **AbstractAppController.php** - Controlador base de la aplicación
- **AbstractRouter.php** - Router base
- **RouteModel.php** - Modelo para rutas

### 🔧 Utilities/ (4 archivos)
**Namespace:** `GPDCore\Utilities`

Utilidades generales y helpers:

- **CSVUtilities.php** - Utilidades para manejo de CSV
- **ImageB64.php** - Utilidades para imágenes en Base64
- **PHPExcelUtilities.php** - Utilidades para Excel
- **UUIDUtilities.php** - Utilidades para UUIDs

### ⚠️ Exceptions/ (3 archivos)
**Namespace:** `GPDCore\Exceptions`

Excepciones personalizadas del sistema:

- **GQLException.php** - Excepción base para GraphQL
- **GQLFormattedError.php** - Formato de errores GraphQL
- **UndefinedTypesException.php** - Excepción para tipos no definidos

## Migración de Código Existente

Si tienes código que referencia el antiguo namespace `GPDCore\Library`, necesitarás actualizar las referencias:

### Antes:
```php
use GPDCore\Library\AppConfig;
use GPDCore\Library\Application;
use GPDCore\Library\EntityUtilities;
use GPDCore\Library\GQLException;
```

### Ahora:
```php
use GPDCore\Core\AppConfig;
use GPDCore\Core\Application;
use GPDCore\Doctrine\EntityMetadataHelper;
use GPDCore\Exceptions\GQLException;
```

## Otras Carpetas en GPDCore/src/

- **Assets/** - Recursos estáticos
- **Controllers/** - Controladores de la aplicación
- **Entities/** - Entidades del modelo de datos
- **Factory/** - Fábricas de servicios
- **Functions/** - Funciones auxiliares
- **Graphql/Types/** - Definiciones de tipos GraphQL
- **Models/** - Modelos de dominio
- **Services/** - Servicios de la aplicación

## Ventajas de la Nueva Estructura

1. **Mejor organización** - Los archivos están agrupados por su propósito funcional
2. **Fácil navegación** - Es más intuitivo encontrar archivos relacionados
3. **Separación de responsabilidades** - Cada carpeta tiene un propósito claro
4. **Escalabilidad** - Más fácil agregar nuevos componentes
5. **Mantenibilidad** - Código más fácil de mantener y entender
6. **Estándares PHP** - Sigue convenciones modernas de organización

## Testing

Todos los tests unitarios e integración siguen funcionando correctamente:
- ✅ 20 tests unitarios pasando
- ✅ 37 assertions exitosas
- ✅ Sin errores de compatibilidad
