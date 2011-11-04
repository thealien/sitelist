<?php

class ECutyCapt extends CApplicationComponent {
	
	public $path;
    public $useXvfb = true;
    public $fileType = 'jpg';
	public $params = array(
        
	);
	
	private $allowedTypes = array (
        'svg','ps','pdf','itext','html','rtree','png','jpeg','jpg','mng','tiff','gif','bmp','ppm','xbm','xpm'
    );
	
	private $allowedParams = array(
        // Minimal width for the image (default: 800)
        'min-width'             => '--min-width',
		// Minimal height for the image (default: 600)
		'min-height'            => '--min-height',
		// Don't wait more than (default: 90000, inf: 0)
		'max-wait'              => '--max-wait',
		// After successful load, wait (default: 0)
		'delay'                 => '--delay',
		// Location of user style sheet, if any
		'user-styles' => '--user-styles',
		// request header; repeatable; some can't be set
		'header' => '--header',
		// Specifies the request method (default: get)
		'method' => '--method',
		// Unencoded request body (default: none)
		'body-string' => '--body-string',
		// Base64-encoded request body (default: none)
		'body-base64' => '--body-base64',
		// appName used in User-Agent; default is none
		'app-name' => '--app-name',
		// appVers used in User-Agent; default is none
		'app-version' => '--app-version',
		// Override the User-Agent header Qt would set
		'user-agent' => '--user-agent',
		// JavaScript execution (default: on)
		'javascript' => '--javascript',
		// Java execution (default: unknown)
		'java' => '--java',
		// Plugin execution (default: unknown)
		'plugins' => '--plugins',
		// Private browsing (default: unknown)
		'private-browsing' => '--private-browsing',
		// Automatic image loading (default: on)
		'auto-load-images' => '--auto-load-images',
		// Script can open windows? (default: unknown)
		'js-can-open-windows' => '--js-can-open-windows',
		// Script clipboard privs (default: unknown)
		'js-can-access-clipboard' => '--js-can-access-clipboard',
		// Backgrounds in PDF/PS output (default: off)
		'print-backgrounds' => '--print-backgrounds'
	);
	
    private $tmpdir = null;
	
	public function init(){
        $this->initPath();
		$this->checkOutputFormat();
		$this->initTmpDir();
		$this->initParams();
    }
	
	public function capture($url){
		$tmpFile = tempnam($this->tmpdir, 'cutycapt') . '.' . $this->fileType;
        $cmdline = '%s %s --url=%s --out=%s';
        $cmdline = sprintf($cmdline,
            $this->path,
			$this->getParamsCommandLine(),
            escapeshellarg($url), 
            escapeshellarg($tmpFile));
        if($this->useXvfb){
            $cmdline = 'xvfb-run --server-args="-screen 0, 1024x768x24" ' . $cmdline;
        }

        exec($cmdline);
        if (file_exists($tmpFile) && filesize($tmpFile)>1) {
            return $tmpFile;
        }
        return false;
	}
	
	protected function initPath(){
		if(!file_exists($this->path)){
            throw new CException('CutyCapt binaty file "'.$this->path.'" does not exist');
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

