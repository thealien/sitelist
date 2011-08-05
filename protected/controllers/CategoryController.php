<?php

class CategoryController extends Controller
{
	public function actionIndex(){
		throw new CHttpException(404);
	}
	/**
	 * Просмотр категории
	 * @param object $id [optional]
	 * @return 
	 */
	public function actionView($id = false, $page = 1){
		$id = intval($id);
		$category = Category::model()->findByPk($id);
        if(!$category){
            throw new CHttpException(404);
        }
        // Настройки пагинации        
        $cat_paginator = false;
        $page = intval($page);
        $page = ($page > 0) ? $page : 1; 
		$pages = new CPagination($category->linksCount);
        $pages->pageSize = 15;
        $pages->route = 'category/view';    
        $cat_paginator = $this->widget(
            'CLinkPager', 
            array_merge(Yii::app()->params['link_pager'], array('pages' => $pages)),
            true
        );
        // Условия выборки сайтов с учетом пагинации
        $links_rel = $category->getActiveRelation('links');
        $links_rel->offset = $pages->offset;
        $links_rel->limit = $pages->limit;

        $lastLinks = Links::getLastLinks(5);
		Yii::app()->params['title'] = $category->catname . ' — ' . Yii::app()->params['title'];
		$this->render('view', array(
            'category'      => $category,
            'lastLinks'     => $lastLinks,
            'cat_paginator' => $cat_paginator
        ));
    }
}