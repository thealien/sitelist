<?php

class ApiController extends Controller
{
    function actionGet($id = false){
    	$result = array('error' => 'Unknown error');
        try{
            if(!is_string($id)) throw new Exception('Error sitename');
            $id = trim($id);
            $l = Links::model()->findByAttributes(array('domain'=>$id));
            if(!$l){
                $id = (stripos($id, 'www.')===false) ? ('www.' . $id) : (str_replace('www.', '', $id));
                $l = Links::model()->findByAttributes(array('domain'=>$id));
            }
            if(!$l) throw new Exception('Site not found');
            $result['error'] = false;
            $result = array_merge($result, $l->attributes);
            if($result['foto']){
                $result['foto_t'] = 'http://f.sitelist.in/t_' . $result['foto'];
                $result['foto'] = 'http://f.sitelist.in/t_' . $result['foto'];
            }
            unset($result['ip'], $result['visible'], $result['userid']);
            //$result['title'] = $this->toUtf($result['title']);
            //$result['desc'] = $this->toUtf($result['desc']);
        }
        catch(exception $e){
            $result['error'] = $e->getMessage();
        }
		$result = json_encode($result);
		header('Content-type: application/json');
    	exit($result);
    }
	
	private function toUtf($s){
		return iconv('windows-1251', 'utf-8', $s);
	}
}