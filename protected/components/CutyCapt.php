<?PHP

class CutyCapt extends CApplicationComponent {
    
    public $path = null;
    public $user_xvfb = true;
    
    public $min_width = 1280;
    
    public $filetype = 'jpg';
    
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
        $cmdline = '%s --min-width=1280 --plugins=on --delay=1500 --url=%s --out=%s';
        if($this->user_xvfb){
            $cmdline = 'xvfb-run --server-args="-screen 0, 1024x768x24" ' . $cmdline;
        }
        $cmd = sprintf($cmdline,
            $this->path,
            escapeshellarg($url), 
            escapeshellarg($tmpfname));
        
        //exit($cmd);
        exec($cmd);
        if (file_exists($tmpfname) && filesize($tmpfname)>1) {
            return $tmpfname;
        }
        return false;
    }
} 




