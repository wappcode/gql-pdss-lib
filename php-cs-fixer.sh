#!/bin/bash

# Script para ejecutar PHP CS Fixer en el contenedor Docker
# Uso:
#   ./php-cs-fixer.sh fix          # Corregir archivos
#   ./php-cs-fixer.sh check        # Solo verificar sin corregir
#   ./php-cs-fixer.sh dry-run      # Ver cambios propuestos sin aplicar

# Nombre del contenedor
CONTAINER_NAME="gqlpdsslib-php8.3"

# Verificar si el contenedor está corriendo
if ! docker ps --filter "name=$CONTAINER_NAME" --format "table {{.Names}}" | grep -q "$CONTAINER_NAME"; then
    echo "Error: El contenedor $CONTAINER_NAME no está corriendo."
    echo "Ejecuta 'docker-compose up -d' en el directorio dockerfiles_gqlpdsslib_php_apache2_mysql8/"
    exit 1
fi

# Comando por defecto
ACTION=${1:-check}

case $ACTION in
    "fix")
        echo "🔧 Ejecutando PHP CS Fixer para corregir archivos..."
        docker exec -it $CONTAINER_NAME composer cs-fix
        ;;
    "check"|"dry-run")
        echo "🔍 Verificando código con PHP CS Fixer..."
        docker exec -it $CONTAINER_NAME composer cs-check
        ;;
    *)
        echo "Uso: $0 [fix|check|dry-run]"
        echo ""
        echo "  fix      - Corregir automáticamente los archivos"
        echo "  check    - Solo verificar sin hacer cambios (por defecto)"
        echo "  dry-run  - Mostrar cambios propuestos sin aplicar"
        exit 1
        ;;
esac