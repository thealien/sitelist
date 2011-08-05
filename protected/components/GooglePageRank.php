<?PHP

class GooglePageRank {
    
    const GMAG = 0xE6359A60;
    //define('GMAG', 0xE6359A60);
    
    private static function zeroFill($a, $b) {
        $z = hexdec(80000000);
        if ($z & $a) {
                $a = ($a>>1);
                $a &= (~$z);
                $a |= 0x40000000;
                $a = ($a>>($b-1));
        } else {
                $a = ($a>>$b);
        }
        return $a;
    }

    private static function GPR_toHex8($intega){
        $Ziffer = "0123456789abcdef";
        return $Ziffer[($intega%256)/16].$Ziffer[$intega%16];
    }

    private static function GPR_hexEncodeU32($num) {
        $result = self::GPR_toHex8(self::zeroFill($num,24));
        $result.= self::GPR_toHex8(self::zeroFill($num,16) & 255);
        $result.= self::GPR_toHex8(self::zeroFill($num,8) & 255);
        return $result . self::GPR_toHex8($num & 255);
    }


    private static function GPR_awesomeHash($value) {
        $GPR_HASH_SEED = "Mining PageRank is AGAINST GOOGLE'S TERMS OF SERVICE. Yes, I'm talking to you, scammer.";
        $kindOfThingAnIdiotWouldHaveOnHisLuggage = 16909125;
        for($i = 0; $i < strlen($value); $i++ ) {
                $kindOfThingAnIdiotWouldHaveOnHisLuggage ^= ord(substr($GPR_HASH_SEED, $i % strlen($GPR_HASH_SEED),1)) ^ ord(substr($value, $i,1));
                $kindOfThingAnIdiotWouldHaveOnHisLuggage = self::zeroFill($kindOfThingAnIdiotWouldHaveOnHisLuggage,23) | $kindOfThingAnIdiotWouldHaveOnHisLuggage << 9;
        }
        return '8'.self::GPR_hexEncodeU32($kindOfThingAnIdiotWouldHaveOnHisLuggage);
    }

    static function getPageRank($url) {
        $url = trim($url);
        $url = parse_url($url);
        if(!isset($url['host']) || !$url['host']){
            return false;
        }
        $url = $url['host'];

        $ch = self::GPR_awesomeHash($url);
        $file = "http://toolbarqueries.google.com/search?client=navclient-auto&features=Rank&ch=$ch&q=info:$url";
        $data = file($file);
        if(!$data || !isset($data[0])){
            return false;
        }
        $rankarray = explode(':', $data[0]);
        if(!isset($rankarray[2])){
            return false;
        }
        $rank = $rankarray[2];
        if (!$rank) $rank=0;
        return $rank;
    }

    static function getPRurl($url) {
        $url = trim($url);
        $url = parse_url($url);
        if(!isset($url['host']) || !$url['host']){
            return false;
        }
        $url = $url['host'];
        
        $ch = self::GPR_awesomeHash($url);
        $prurl = "http://toolbarqueries.google.com/search?client=navclient-auto&features=Rank&ch=$ch&q=info:$url";
        return $prurl;
    }

}

