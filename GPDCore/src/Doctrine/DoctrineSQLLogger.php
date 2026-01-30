<?php

declare(strict_types=1);

namespace GPDCore\Doctrine;

use GPDCore\Core\AppConfig;

/**
 * Logger para queries SQL de Doctrine.
 * 
 * Nota: SQLLogger está deprecado en Doctrine DBAL 3+.
 * Esta clase mantiene compatibilidad pero puede necesitar migración a PSR-3 Logger.
 */
class DoctrineSQLLogger
{
    private float $startTime = 0.0;
    private ?string $logDirectory = null;

    /**
     * Constructor.
     *
     * @param string|null $logDirectory Directorio donde guardar los logs. Si es null, usa AppConfig.
     */
    public function __construct(private AppConfig $config, ?string $logDirectory = null)
    {
        $this->logDirectory = $logDirectory;
    }

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string              $sql    the SQL to be executed
     * @param mixed[]|null        $params the SQL parameters
     * @param int[]|string[]|null $types  the SQL parameter types
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        $this->startTime = microtime(true);
        $strDate = date('Y-m-d H:i:s');
        $strparams = var_export($params, true);
        $msg = <<<QUERY
 ############## START QUERY ################### 
Inicio: {$strDate} 
Query: {$sql} 
Params: {$strparams}

QUERY;
        $this->writeLog($msg);
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     */
    public function stopQuery(): void
    {
        $endTime = microtime(true);
        $strDate = date('Y-m-d H:i:s');
        $duration = $endTime - $this->startTime;
        $timeMs = round($duration * 1000, 2);
        $timeSec = round($duration, 4);

        $msg = <<<QUERY
Termino: {$strDate} 
Tiempo: {$timeSec} segundos ({$timeMs} ms)
############## END QUERY ################### 

QUERY;
        $this->writeLog($msg);
    }

    protected function writeLog(string $msg): void
    {
        $dir = $this->logDirectory ?? $this->config->get('sql_log_dir');
        $filename = 'doctrine.log';
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        // Crear directorio si no existe
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $result = @file_put_contents($path, $msg, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            error_log("Failed to write SQL log to: {$path}");
        }
    }
}
