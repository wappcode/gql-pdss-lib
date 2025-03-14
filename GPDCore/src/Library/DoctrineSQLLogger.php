<?php

namespace GPDCore\Library;

use GPDCore\Services\ConfigService;
use DateTime;

class DoctrineSQLLogger implements DoctrineSQLLogger
{

    private $startTime;
    /**
     * Logs a SQL statement somewhere.
     *
     * @param string              $sql    The SQL to be executed.
     * @param mixed[]|null        $params The SQL parameters.
     * @param int[]|string[]|null $types  The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $currentDate = new DateTime();
        $strDate = $currentDate->format("Y-M-d H:i:s");
        $this->startTime = $currentDate->getTimestamp();
        $strparams = var_export($params, true);
        $msg = <<<QUERY
 ############## SART QUERY ################### 
Inicio: {$strDate} 
Query: {$sql} 
Params: {$strparams}

QUERY;
        $this->writeLog($msg);
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        $currentDate = new DateTime();
        $strDate = $currentDate->format("Y-M-d H:i:s");
        $endTime = $currentDate->getTimestamp();
        $time = ($endTime - $this->startTime) / 1000;

        $msg = <<<QUERY
Termino: {$strDate} 
Tiempo: {$time} segundos
############## END QUERY ################### 

QUERY;
        $this->writeLog($msg);
    }

    protected function writeLog(string $msg)
    {
        $dir = ConfigService::getInstance()->get('sql_log_dir');
        $filename = 'doctrine.log';
        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        @file_put_contents($path, $msg, FILE_APPEND | LOCK_EX);
    }
}
