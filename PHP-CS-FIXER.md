# PHP CS Fixer - Configuración

Este proyecto está configurado con PHP CS Fixer para mantener un estilo de código consistente siguiendo los estándares PSR-12 y Symfony.

## 🚀 Uso Rápido

### Usando el script de bash (Recomendado)
```bash
# Verificar el código sin hacer cambios
./php-cs-fixer.sh check

# Corregir automáticamente todos los archivos
./php-cs-fixer.sh fix

# Ver cambios propuestos sin aplicar (dry-run)
./php-cs-fixer.sh dry-run
```

### Usando Composer directamente en el contenedor Docker
```bash
# Desde el directorio dockerfiles_gqlpdsslib_php_apache2_mysql8/
docker-compose exec gqlpdsslib-php composer cs-check   # Solo verificar
docker-compose exec gqlpdsslib-php composer cs-fix     # Corregir archivos
docker-compose exec gqlpdsslib-php composer cs-fix-dry # Ver cambios propuestos
```

### Comando directo de PHP CS Fixer
```bash
# Ejecutar PHP CS Fixer directamente en el contenedor
docker-compose exec gqlpdsslib-php vendor/bin/php-cs-fixer fix --dry-run --diff
docker-compose exec gqlpdsslib-php vendor/bin/php-cs-fixer fix
```

## 📁 Archivos y Directorios

- **`.php-cs-fixer.php`**: Archivo de configuración principal
- **`.php-cs-fixer.cache`**: Cache de PHP CS Fixer (ignorado en git)
- **`php-cs-fixer.sh`**: Script de conveniencia para ejecutar en Docker

## 🔧 Reglas Configuradas

El proyecto usa las siguientes reglas:
- **@PSR12**: Estándar PSR-12 completo
- **@Symfony**: Reglas adicionales de Symfony
- **Sintaxis de arrays corta**: `[]` en lugar de `array()`
- **Espaciado consistente** en operadores y paréntesis
- **Eliminación de imports no utilizados**
- **Formato consistente de PHPDoc**
- **Y muchas más...**

## 🚫 Directorios Excluidos

PHP CS Fixer ignora automáticamente:
- `vendor/`
- `dockerfiles_gqlpdsslib_php_apache2_mysql8/`
- `var/`
- `cache/`

## 🔄 Integración con el Workflow

### Pre-commit (Manual)
Antes de hacer commit, ejecuta:
```bash
./php-cs-fixer.sh check
```

### CI/CD
Puedes agregar esto a tu pipeline de CI:
```bash
composer cs-check
```

## ⚙️ Personalización

Para modificar las reglas, edita el archivo `.php-cs-fixer.php` y ajusta el array de rules según tus necesidades.

## 🔍 Ejemplos de Salida

### Verificación exitosa:
```
🔍 Verificando código con PHP CS Fixer...
Loaded config default from ".php-cs-fixer.php".
No files need fixing.
```

### Archivos que necesitan corrección:
```
🔍 Verificando código con PHP CS Fixer...
   1) GPDCore/src/Services/SomeService.php
   2) GPDCore/src/Controllers/SomeController.php
```

### Corrección aplicada:
```
🔧 Ejecutando PHP CS Fixer para corregir archivos...
Fixed 2 of 2 files in 0.234 seconds, 12.000 MB memory used
```

## 📚 Recursos Adicionales

- [Documentación oficial de PHP CS Fixer](https://cs.symfony.com/)
- [Lista completa de reglas](https://cs.symfony.com/doc/rules/index.html)
- [Configuración de reglas](https://cs.symfony.com/doc/config.html)