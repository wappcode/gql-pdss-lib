# GPDCore - Estructura de Carpetas

Este documento describe la organización del código en el directorio `GPDCore/src/`.

## Estructura Reorganizada

A partir de la versión actual, todos los archivos que anteriormente estaban en `Library/` han sido reorganizados en carpetas temáticas para mejorar la mantenibilidad y claridad del código.

### 📋 Contracts/ (14 archivos)
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
- **FrameworkHandler.php** - Manejador principal del framework
- **GPDApp.php** - Clase principal de la aplicación
- **MiddlewareQueue.php** - Implementación de cola de middlewares
- **ResolverManager.php** - Gestor de resolvers GraphQL
- **SchemaManager.php** - Gestor de schemas GraphQL
- **TypesManager.php** - Gestor de tipos GraphQL

### 🔷 Graphql/ (8 archivos)
**Namespace:** `GPDCore\Graphql`

Componentes específicos para GraphQL:

- **AbstractCustomTypeFactory.php** - Fábrica base para tipos personalizados
- **ConnectionQueryResponse.php** - Respuestas de consultas con conexiones
- **ConnectionTypeFactory.php** - Fábrica para tipos de conexión
- **DefaultArrayResolver.php** - Resolver por defecto para arrays
- **DefaultDoctrineFieldResolver.php** - Resolver por defecto para campos Doctrine
- **FieldResolveFactory.php** - (Deprecado: Fusionado con ResolverFactory.php)
- **GraphqlSchemaUtilities.php** - Utilidades para schemas GraphQL
- **ResolverFactory.php** - Fábrica para resolvers

### 💾 Doctrine/ (8 archivos)
**Namespace:** `GPDCore\Doctrine`

Utilidades y componentes relacionados con Doctrine ORM:

- **ArrayToEntity.php** - Conversión de arrays a entidades
- **DoctrineSQLLogger.php** - Logger para consultas SQL
- **EntityAssociation.php** - Gestión de asociaciones de entidades
- **EntityBuffer.php** - Buffer para entidades (N+1 prevention)
- **EntityUtilities.php** - Utilidades generales para entidades
- **QueryBuilderHelper.php** - Helper para QueryBuilder y manejo de asociaciones de Doctrine
- **ProxyUtilities.php** - Utilidades para proxies de Doctrine
- **QueryDecorator.php** - Decorador para consultas

### 🛣️ Routing/ (3 archivos)
**Namespace:** `GPDCore\Routing`

Componentes de enrutamiento y controladores:

- **AbstractAppController.php** - Controlador base de la aplicación
- **AbstractRouter.php** - Router base
- **RouteModel.php** - Modelo para rutas

### 🔧 Utilities/ (5 archivos)
**Namespace:** `GPDCore\Utilities`

Utilidades generales y helpers:

- **CollectionBuffer.php** - Buffer para colecciones
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
use GPDCore\Library\GPDApp;
use GPDCore\Library\EntityUtilities;
use GPDCore\Library\GQLException;
```

### Ahora:
```php
use GPDCore\Core\AppConfig;
use GPDCore\Core\GPDApp;
use GPDCore\Doctrine\EntityUtilities;
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
