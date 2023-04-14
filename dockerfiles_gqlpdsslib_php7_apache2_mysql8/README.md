Iniciar servidor php7.3 y mysql5 Docker
----------

Para iniciar el servidor usar el comando

    docker-compose up

Si hay cambios en las imágenes usar el siguiente comando para compilarlas

    docker-compose build


## Crear base de datos

- Iniciar servicios
- Ingresar a la linea de comandos mysql y dar de alta la base de datos

    mysql -h 127.0.0.1 -uroot -pdbpassword procesot_survey < ~/archivosbd.sql

## Iniciar librerias de php

Ingresar a bash del servicio php 

    docker exec -it talentotoyota-php7.3 bash
    
Ejecutar el comando para instalar librerias

    composer install

Si hay error por que el archivo vendor/zendframework/zendframework/library/Zend/Session/AbstractContainer.php no implementa correctamente ArrayObject buscar la siguiente linea 435

    public function offsetGet($key)

y reemplazar por

    public function &offsetGet($key)


## Xdebug

Para VSCode agregar la siguiente configuración en launch.json

     {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            }
    },