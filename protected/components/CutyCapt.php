<?PHP

class CutyCapt extends CApplicationComponent {
    
    public $path = null;
    public $user_xvfb = true;
    public $min_width = 1280;
    public $filetype = 'jpg';
	public $user_agent;
    
    private $allow_ext = array (
        'svg','ps','pdf','itext','html','rtree','png','jpeg','jpg','mng','tiff','gif','bmp','ppm','xbm','xpm'
    );
    
    private $tmpdir = null;
    
    public function init(){
        if(!file_exists($this->path)){
            throw new CException('CutyCapt binaty file "'.$this->path.'" does not exist');
        }
        if(!in_array($this->filetype, $this->allow_ext)){
            throw new CException('Output filetype does not allow. Choose on from: ' . implode(', ', $this->allow_ext));
        }
        $this->tmpdir = sys_get_temp_dir();
        if(!is_writable($this->tmpdir)){
            throw new CException('System TMP dir "' . $this->tmpdir . '" is does not writable');
        }
    }
    
    public function capture($url){
        $tmpfname = tempnam($this->tmpdir, 'cutycapt') . '.' . $this->filetype;
        $cmdline = '%s --min-width=1280 --plugins=on --delay=2000 --url=%s --out=%s';
		$cmdline = sprintf($cmdline,
            $this->path,
            escapeshellarg($url), 
            escapeshellarg($tmpfname));
		if($this->user_agent){
			$cmdline .= sprintf(' --user-agent=%s', escapeshellarg($this->user_agent));
		}
		if($this->user_xvfb){
            $cmdline = 'xvfb-run --server-args="-screen 0, 1024x768x24" ' . $cmdline;
        }
        
        //exit($cmd);
        exec($cmdline);
        if (file_exists($tmpfname) && filesize($tmpfname)>1) {
            return $tmpfname;
        }
        return false;
    }
} 




