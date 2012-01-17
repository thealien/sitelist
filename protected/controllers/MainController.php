<?php

class MainController extends Controller
{

	/**
	 * Страница результата поиска сайтов
	 * @return 
	 */
    public function actionSearch($q = false){
        $error = false;
        $links = array();
		$total_found = 0;
        $psize = 15;
		$page = Yii::app()->request->getParam('page');
		if(!$page) $page = 1;
		do {
			if($q===false) break;
			if(mb_detect_encoding($q)===false)
                throw new CHttpException(404);
			$q = @iconv('utf-8//IGNORE', 'utf-8', $q);
			if(!$q)
                throw new CHttpException(404);
			$q = trim($q);
			if(!$q){
				$error = 'Указан пустой поисковый запрос.';
				break;
			}
			if(strlen($q)<2){
				$error = 'Поисковый запрос должен состоять минимум из 2 символов.';
                break;
			}
			$sphinx = new SphinxClient();
            $sphinx->SetServer(Yii::app()->params['sphinx']['host'], Yii::app()->params['sphinx']['port']);
			if(!$sphinx->_Connect()){
				$error = 'В данный момент служба поиска недоступна. Попробуйте позже.';
                break;
			}
			$sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);
            $sphinx->SetSortMode(SPH_SORT_RELEVANCE);
            $sphinx->setFieldWeights(array(
                'url'   => 3,
                'title' => 2,
                'desc'  => 1
            ));
            $sphinx->SetLimits($psize*($page-1), $psize);
            $query = "@(url,title,desc)".$sphinx->escapeString(sprintf('%s',$q));
            $result = $sphinx->Query($query, 'linksIndex');
            if ($result === false) {
            	$error = 'В данный момент служба поиска недоступна. Попробуйте позже.';
				//echo "Query failed: " . $sphinx->GetLastError() . ".\n";
                break;
            }
            else{
                if ($sphinx->GetLastWarning()) {
                    //echo "WARNING: " . $sphinx->GetLastWarning() . "";
                }
                if (!empty($result["matches"]) ) {
                    $matches = $result['matches'];
					$total_found = $result['total_found'];
                    $ids = array_keys($matches);
                    $tmp = Links::model()->findAllByPk($ids);
                    foreach($tmp as $l){
                        $matches[$l->id] = $l;
                    }
                    $links = $matches;
                }
				else{
					$error = 'Ничего не найдено.';
				}
            }
		}
        while(false);
		$pages = new CPagination($total_found);
        $pages->pageSize = $psize;

        $lastLinks = Links::getLastLinks(3, true);
        Yii::app()->params['title'] = 'Поиск'.($q?' "'.$q.'"':'').' — ' . Yii::app()->params['title'];
        $this->render('search', array(
            'lastLinks'         => $lastLinks,
            'q'                 => $q,
            'links'             => $links,
            'error'             => $error,
            'pages'             => $pages
        ));
    }
	/**
	 * Главная страница
	 * @return 
	 */
	public function actionIndex()
	{
		$categories = Category::getRootCats();
        $lastLinks = Links::getLastLinks(3);
		
		$this->render('index', array(
            'categories'    =>$categories,
			'lastLinks'     =>$lastLinks
		));
	}
    /*
     * Просмотр странички "О сайте"
     * @return 
     */
    public function actionAbout()
    {
        $lastLinks = Links::getLastLinks(2);
        Yii::app()->params['title'] = 'О проекте — ' . Yii::app()->params['title'];
        $this->render('about', array(
            'lastLinks'     =>$lastLinks
        ));
    }
    /**
     * Выдача RSS-ленты
     * @param string $category [optional] - имя категории
     * @return 
     */
    public function actionRss($category = false){
    	$cat = false;
    	if($category){
            $category = trim(strval($category));
			$cat = Category::model()->findByAttributes(array('alias'=>$category));
			$category = isset($cat->id);
    	}
        $links = Links::model()->findAll(array(
            'condition' => ($category ? "catid=".$cat->id.' AND ' : '') . 'visible=1',
            'order' => 'id DESC',
            'limit' => 20            
        ));
        
        header("content-type: application/rss+xml");
        $this->render('rss', array(
            'links' => $links,
            'cat'   => $cat
        ));
		exit();
    }
    
    public function actionError(){
        $error = Yii::app()->errorHandler->error;
        if(!$error) $this->redirect('/', true, 302);
        $tpl = '404';
        switch($error['code']){
            case 404:
				Yii::app()->params['title'] = 'Ошибка 404 — ' . Yii::app()->params['title'];
            	$tpl = '404';
            	break;
            /*case 500:
                $tpl = '500';
                break;
            case 501:
                $tpl = '501';
                break;
            case 502:
                $tpl = '502';
                break;
            case 503:
                $tpl = '503';
                break;
            case 504:
                $tpl = '504';
                break;*/
            default: $this->redirect('/', true, 302);
        };
        $this->render($tpl, $error);
    }
	
	public function actionUsers($page = 1)
    {
        $page = intval($page);
        $page = ($page > 0) ? $page : 1;
        
        $criteria = new CDbCriteria();
        $count = Users::model()->count($criteria);
		$criteria->order= 'UserID DESC';
        
        $pages = new CPagination($count);
        $pages->pageSize = 30;
		$pages->applyLimit($criteria);
        
        $users = Users::model()->with('profile')->findAll($criteria);

        Yii::app()->params['title'] =  'Пользователи' . ' — ' . Yii::app()->params['title'];
        $this->render('users', array(
            'users'             =>$users,
            'pages'              => $pages
        ));
    }
}
