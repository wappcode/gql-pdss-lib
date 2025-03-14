<?php
echo "\n Preparando para inicializar base de datos \n";
$user = getenv("PDSSLIB_DBUSER") ? getenv("PDSSLIB_DBUSER") : 'root';
$pass = getenv("PDSSLIB_DBPASSWORD") ?  getenv("PDSSLIB_DBPASSWORD") : 'dbpassword';
$host = getenv("PDSSLIB_DBHOST") ?  getenv("PDSSLIB_DBHOST") : 'localhost';
$databasename = getenv("PDSSLIB_DBNAME") ?  getenv("PDSSLIB_DBNAME") : 'gqlpdss_pdsslib';
$pdo = new PDO("mysql:host={$host};dbname=", $user, $pass);
echo "\n Limpiando base de datos {$databasename} \n";
$pdo->beginTransaction();
$pdo->exec("DROP DATABASE IF EXISTS {$databasename};");
echo "\n Creando base de datos {$databasename};";
$pdo->exec("CREATE DATABASE IF NOT EXISTS {$databasename};");
$pdo->commit();
echo "\n";
