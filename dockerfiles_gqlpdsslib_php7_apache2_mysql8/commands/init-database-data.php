<?php
ini_set("display_error", 1);
error_reporting(E_ALL);

$user = getenv("PDSSLIB_DBUSER") ? getenv("PDSSLIB_DBUSER") : 'root';
$pass = getenv("PDSSLIB_DBPASSWORD") ?  getenv("PDSSLIB_DBPASSWORD") : 'dbpassword';
$host = getenv("PDSSLIB_DBHOST") ?  getenv("PDSSLIB_DBHOST") : 'localhost';
$databasename = getenv("PDSSLIB_DBNAME") ?  getenv("PDSSLIB_DBNAME") : 'gqlpdsslib';
$pdo = new PDO("mysql:host={$host};dbname={$databasename}", $user, $pass);
echo "\n Preparando para insertar datos en la  base de datos {$databasename} \n";
$sql = file_get_contents(__DIR__ . "/gqlpdss_pdsslib.sql");
echo "\n";
if (empty($sql)) {
    echo "\n No hay datos que insertar";
    exit;
}
$pdo->beginTransaction();
echo "\n Insertando datos {$databasename};\n";
echo $sql;
try {
    $pdo->query($sql);
    echo "\n Datos insertados\n";
    $pdo->commit();
} catch (Exception $e) {
    echo $e->getMessage();
    $pdo->rollBack();
}
echo "\n";
