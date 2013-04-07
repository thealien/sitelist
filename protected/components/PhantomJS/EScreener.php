<?php

class EScreener extends CApplicationComponent {

    public $path;
    public $script;
    public $useXvfb = false;
    public $fileType = 'jpg';
    public $params = array(

    );

    private $allowedTypes = array (
        'pdf', 'png','jpeg','jpg','gif'
    );

    private $allowedParams = array(
        'width'             => 'width',
        'height'            => 'height'
    );

    private $tmpdir = null;

    public function init(){
        $this->initPath();
        $this->checkOutputFormat();
        $this->initTmpDir();
        $this->initParams();
        if (is_null($this->script) || !file_exists($this->script)) {
            $this->script = dirname(__FILE__) . '/screener.js';
        }
    }

    public function capture($url){
        $tmpFile = tempnam($this->tmpdir, 'phjs') . '.' . $this->fileType;
        $cmdline = '%s %s url=%s filename=%s %s';
        $cmdline = sprintf($cmdline,
            $this->path,
            $this->script,
            escapeshellarg($url),
            escapeshellarg($tmpFile),
            $this->getParamsCommandLine()
        );
        exec($cmdline);
        if (file_exists($tmpFile) && filesize($tmpFile)>1) {
            return $tmpFile;
        }
        return false;
    }

    protected function initPath(){
        if(!file_exists($this->path)){
            throw new CException('PhantomJS binary file "'.$this->path.'" does not exist');
        }
    }

    protected function checkOutputFormat(){
        if(!in_array($this->fileType, $this->allowedTypes)){
            throw new CException('Output filetype does not allow. Choose on from: ' . implode(', ', $this->allowedTypes));
        }
    }

    protected function initTmpDir(){
        $this->tmpdir = sys_get_temp_dir();
        if(!is_dir($this->tmpdir)){
            throw new CException('TMP dir "' . $this->tmpdir . '" is not a directory');
        }
        if(!is_writable($this->tmpdir)){
            throw new CException('TMP dir "' . $this->tmpdir . '" does not writable');
        }
    }

    protected function initParams(){
        $params = array();
        foreach($this->params as $k=>$v){
            if(!array_key_exists($k, $this->allowedParams)) {
                continue;
            }
                //throw new CException('Parameter "' . $k . '" does not allowed');
            if(!is_string($v) && !is_numeric($v)) {
                continue;
            }
                //throw new CException('Parameter "' . $k . '" must be String or Numeric');
            $params[$k] = $v;
        }
        $this->params = $params;
    }

    protected function getParamsCommandLine(){
        $s = '';
        $p = array();
        foreach($this->params as $k=>$v){
            if(!is_numeric($v)) {
                $v = escapeshellarg(trim($v));
            }
            $p[] = sprintf('%s=%s', $this->allowedParams[$k], $v);
        }
        if(!empty($p)) $s = implode(' ', $p);
        return $s;
    }


}

