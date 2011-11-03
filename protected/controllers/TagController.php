<?php

class TagController extends Controller{
	
	public function actionIndex(){
		throw new CHttpException(404);
	}	
	
	public function actionView($tag){
		$exists = Tag::model()->exists(array(
            'condition'=>'name = :name',
            'params' => array( ':name' => $tag ) 
		));
		if(!$exists)
            throw new CHttpException(404);
		$links = Links::model()->taggedWith($tag)->findAll();
		Yii::app()->params['title'] =  'Сайты с тегом "' . $tag . '" — ' . Yii::app()->params['title'];
		
		$this->render('view', array(
            'links' => $links,
            'tag' => $tag
		));
	}
	
}