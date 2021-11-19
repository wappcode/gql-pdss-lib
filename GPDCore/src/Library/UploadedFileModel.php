<?php 

namespace GPDCore\Library;

class UploadedFileModel {
    protected $realName;

    protected $finalPath;

    protected $fileName;

    public function __construct($realName, $finalPath, $fileName)
    {
        $this->realName = $realName;
        $this->finalPath = $finalPath;
        $this->fileName = $fileName;
    }



    /**
     * Get the value of realName
     */ 
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * Set the value of realName
     *
     * @return  self
     */ 
    public function setRealName($realName)
    {
        $this->realName = $realName;

        return $this;
    }

    /**
     * Get the value of finalPath
     */ 
    public function getFinalPath()
    {
        return $this->finalPath;
    }

    /**
     * Set the value of finalPath
     *
     * @return  self
     */ 
    public function setFinalPath($finalPath)
    {
        $this->finalPath = $finalPath;

        return $this;
    }

    /**
     * Get the value of fileName
     */ 
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set the value of fileName
     *
     * @return  self
     */ 
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }
}