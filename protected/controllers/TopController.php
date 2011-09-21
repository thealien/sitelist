<?php

class TopController extends Controller
{
	/**
	 * Рейтинг сайтов, n-я страница
	 * @param int $page [optional]
	 * @return 
	 */
	public function actionIndex($page = 1)
    {
    	$page = intval($page);
		$page = ($page > 0) ? $page : 1;
		
		$count = Links::model()->count(array(
            'condition' => 'visible = 1'
		));
		
		$pages = new CPagination($count);
        $pages->pageSize = 20;
		
		$links = Links::getLinkByRate(($page-1)*$pages->pageSize, $pages->pageSize);
        $lastLinks = Links::getLastLinks(3);
		
		Yii::app()->params['title'] =  'Рейтинг сайтов' . ' — ' . Yii::app()->params['title'];
        $this->render('view', array(
            'links'             =>$links,
            'lastLinks'         =>$lastLinks,
            'page'              => $page,
			'pages' => $pages
        ));
    }
}