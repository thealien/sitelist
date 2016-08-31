<?php

class EWebshot extends CApplicationComponent {

    public $path;
    public $useXvfb = true;
    public $fileType = 'jpg';
    public $params = array(

    );

    private $allowedTypes = array (
        'png','jpeg','jpg'
    );

    private $allowedParams = array(
        'window-size'   => '--window-size',
        'shot-size'     => '--shot-size',
        'shot-offset'   => '--shot-offset',
        'phantom-path'  => '--phantom-path',
        'custom-header' => '--custom-header',
        'custom-css'    => '--custom-css',
        'quality'       => '--quality',
        'stream-type'   => '--stream-type',
        'render-delay'  => '--render-delay',
        'timeout'       => '--timeout',
        'shot-callback' => '--shot-callback',
        'http-error'    => '--http-error',
        'default-white-background' => '--default-white-background'
    );

    private $tmpdir = null;

    public function init(){
        $this->checkOutputFormat();
        $this->initTmpDir();
        $this->initParams();
    }

    public function capture($url){
        $tmpFile = tempnam($this->tmpdir, 'webshot') . '.' . $this->fileType;
        $cmdline = '%s --default-white-background %s %s %s';
        $cmdline = sprintf($cmdline,
            $this->path,
            $this->getParamsCommandLine(),
            escapeshellarg($url),
            escapeshellarg($tmpFile));
        if($this->useXvfb){
            //$cmdline = 'xvfb-run --server-args="-screen 0, 1024x768x24" ' . $cmdline;
        }
        // exit($cmdline);
        exec($cmdline);
        if (file_exists($tmpFile) && filesize($tmpFile)>1) {
            return $tmpFile;
        }
        return false;
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
            throw new CException('TMP dir "' . $this->tmpdir . '" not writable');
        }
    }

    protected function initParams(){
        foreach($this->params as $k=>$v){
            if(!key_exists($k, $this->allowedParams))
                throw new CException('Parameter "' . $k . '" does not allowed');
            if(!is_string($v) && !is_numeric($v))
                throw new CException('Parameter "' . $k . '" must be String or Numeric');
        }
    }

    protected function getParamsCommandLine(){
        $s = '';
        $p = array();
        foreach($this->params as $k=>$v){
            $p[] = sprintf('%s=%s', $this->allowedParams[$k], escapeshellarg(trim($v)));
        }
        if(!empty($p)) $s = implode(' ', $p);
        return $s;
    }

}

