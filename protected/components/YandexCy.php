<?PHP

class YandexCy {
    
    static function getCy($url){
        $url = trim($url);
        $url = parse_url($url);
        if(!isset($url['host']) || !$url['host']){
            return false;
        }
        $url = $url['host'];
        
        $cy_template = 'http://bar-navig.yandex.ru/u?ver=2&show=32&url=http://%s/';
        $cy_url = sprintf($cy_template, $url);
        $cy_data = implode("", file("$cy_url"));
        $cy = array();
        preg_match("/value=\"(.\d*)\"/", $cy_data, $cy);
        if ($cy[1] == ""){
            return false;
        }
        return intval($cy[1]);
    } 
}