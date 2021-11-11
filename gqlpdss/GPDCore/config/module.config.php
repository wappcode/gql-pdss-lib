<?php 

// Agregar a todas las claves el prefijo core
return [
    'core_upload_dir' => realpath(__DIR__."/../../../public/uploads"),
    'core_upload_tmp_dir' => realpath(__DIR__."/../../../public/uploads/tmp"),
    'sql_log_dir' => realpath(__DIR__."/../../../logs"),
    'query_limit' => 1000

];