<?php

namespace GPDCore\Services;

use GPDCore\Library\GQLException;
use Exception;
use Flow\Basic;
use Flow\Config;
use Flow\Request;

use GPDCore\Library\UploadedFileModel;

use function GPDCore\Functions\getFileExtension;
use function GPDCore\Functions\getFileType;
use function GPDCore\Functions\getFileNameOnly;
use function GPDCore\Functions\standardizeFileName;

class UploadFileService {

    const FILENAME_SEPARATOR = '----.';

    public static function uploadFile(): ?UploadedFileModel {
        $tmpDir = ConfigService::getInstance()->get('core_upload_tmp_dir');
        $config = new Config();
        $config->setTempDir($tmpDir);
        $request = new Request();
        $uploadFolder = $tmpDir;
        $requestName = $request->getFileName();
        $standardizeName = standardizeFileName($requestName);
        $uploadFileName = uniqid().self::FILENAME_SEPARATOR.$standardizeName; // The name the file will have on the server
        $uploadPath = $uploadFolder.DIRECTORY_SEPARATOR.$uploadFileName;
        if (Basic::save($uploadPath, $config, $request)) {
            return new UploadedFileModel($requestName,$uploadPath,$standardizeName);
        } else {
            return null;
        }
    }
    /**
     * Mueve y/o renombra un archivo desde el directorio temporal a la ubicación especificad
     * Los directorios base esta establecido en la configuracion de CoreConfigService con las clave core_upload_tmp_dir core_upload_dir
     * @param $src string relativePath del archivo origen
     * @param $dest string relativePath del archivo destino
     * @param $overwrite bool cuando el valor es verdadero y existe un archivo con el mismo nombre que el destino lo sobreescribe
     * @return bool Retorna true si fue movido o renombrado false en caso contrario 
     */
    public static function mvFile(string $src, string $dest, bool $overwrite = false): bool {
        $filename = getFileNameOnly($src);
        $srcPath = ConfigService::getInstance()->get('core_upload_tmp_dir').DIRECTORY_SEPARATOR.$filename;
        $destPath = ConfigService::getInstance()->get('core_upload_dir').DIRECTORY_SEPARATOR.$dest;
        if(!file_exists($srcPath) || is_dir($srcPath)) {
            throw new Exception("El archivo origen no existe", 400);
        }
        if(file_exists($destPath)){
            if($overwrite) {
                @unlink($destPath);
            } else {
                throw new Exception("El nombre del archivo esta duplicado", 400);
            }
        }
        $ok = @rename($srcPath, $destPath);
        return $ok;
        
        
    }

    /**
     * Obtiene el nombre del archivo cargado sin texto adicional
     * @return string
     */
    public static function getUploadedFileNameOnly($src): string {
        $filename = getFileNameOnly($src);
        $pos =strripos($filename, self::FILENAME_SEPARATOR);
        $filename = ($pos===false) ? $filename:  substr($filename, $pos);
        $filename = str_replace(self::FILENAME_SEPARATOR, '', $filename);
        return $filename;

    }
    /**
     * Elimina un archivo. El directorio base esta establecido en la configuracion de CoreConfigService con la clave core_upload_dir
     * @param $src string relativePath del archivo origen
     * @return bool Retorna true si fue eliminado false en caso contrario 
     */
    public static function rmFile(string $src) {
        $path = ConfigService::getInstance()->get('core_upload_dir').DIRECTORY_SEPARATOR.$src;
        if(!file_exists($path) || is_dir($path)) {
            throw new Exception("El archivo origen no existe", 400);
        }
        $ok = @unlink($path);
        return $ok;
    }
    /**
     * Elimina un archivo temporal el directorio base esta establecido en la configuracion de CoreConfigService con la clave core_upload_tmp_dir
     * @param $filename string relativePath del archivo a eliminar
     * @return bool Retorna true si fue eliminado false en caso contrario 
     */
    public static function rmTmpFile(string $filename) {
        // obtiene solo el nombre del archivo
        $filename = getFileNameOnly($filename);
        $path = ConfigService::getInstance()->get('core_upload_tmp_dir').DIRECTORY_SEPARATOR.$filename;
        if(!file_exists($path) || is_dir($path)) {
            throw new Exception("El archivo origen no existe", 400);
        }
        $ok = @unlink($path);
        return $ok;
    }
    /**
     * Lee el contenido de un archivo  y aplica las cabeceras correspondientes para poderlo mostrar en un navegador
     * El directorio base esta establecido en la configuracion de CoreConfigService con la clave core_upload_dir
     * @param $src string relativePath del archivo origen
     * @return void
     */
    public static function readFile(string $src, string $fileName = '') {
        $path = ConfigService::getInstance()->get('core_upload_dir').DIRECTORY_SEPARATOR.$src;
        if(!file_exists($path) || is_dir($path)){
            throw new Exception("El archivo no existe", 404);
        }
        if(empty($fileName)) {
            $fileName = getFileNameOnly($path);
        } else {
            $extension = getFileExtension($path);
            $fileName = $fileName.".".$extension;

        }
        $contentType = getFileType($path);
        $fileName = getFileNameOnly($path);
        $size = filesize($path);
        header("Content-disposition: filename=$fileName");
        header("Content-type: $contentType");
        header('Cache-Control: max-age=86400');
        header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
        header('Content-Length: ' . $size);
        readfile($path);
        exit;
    }
    /**
     * Lee el contenido de un archivo  y aplica las cabeceras correspondientes para poderlo descargar en un navegador
     * El directorio base esta establecido en la configuracion de CoreConfigService con la clave core_upload_dir
     * @param $src string relativePath del archivo origen
     * @return void
     */
    public static function downloadFile(string $src, string $fileName = '') {
        $path = ConfigService::getInstance()->get('core_upload_dir').DIRECTORY_SEPARATOR.$src;
        if (!file_exists($path) || is_dir($path)) {
            throw new Exception("El archivo no existe", 404);
        }
        if(empty($fileName)) {
            $fileName = getFileNameOnly($path);
        } else {
            $extension = getFileExtension($path);
            $fileName = $fileName.".".$extension;

        }
        $contentType ='application/octet-stream';
        $fileName = getFileNameOnly($path);
        $size = filesize($path);
        header("Content-disposition: attachment; filename=$fileName");
        header("Content-type: $contentType");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: max-age=86400');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
        header('Content-Length: ' . $size);
        readfile($path);
        exit;
    }
    public static function uploadB64File(string $content, string $src) {
        $destPath = ConfigService::getInstance()->get('core_upload_dir').DIRECTORY_SEPARATOR.$src;
        if(file_exists($destPath)){
            throw new GQLException("El nombre del archivo esta duplicado", 400);
        }
          // open the output file for writing
          $ifp = fopen( $destPath, 'wb' ); 
    
          // split the string on commas
          // $data[ 0 ] == "data:image/png;base64"
          // $data[ 1 ] == <actual base64 string>
          $data = explode( ',', $content );
      
          // we could add validation here with ensuring count( $data ) > 1
          fwrite( $ifp, base64_decode( $data[ 1 ] ) );
      
          // clean up the file resource
          fclose( $ifp ); 
      
          return $destPath; 
    }
}