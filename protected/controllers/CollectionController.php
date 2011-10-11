<?php

class CollectionController extends Controller
{
	/**
	 * Добавление подборки
	 * @return 
	 */
	public function actionAdd(){
		if(Yii::app()->user->isGuest)
            $this->redirect(array('user/login'));
		$collection = new Collection();
        if(isset($_POST['Collection'])){
            $collection->attributes = $_POST['Collection'];
			$collection->user_id = Yii::app()->user->id;
            if($collection->save())
                $this->redirect(array('user/index', 'user'=> Yii::app()->user->name));
        }
		$this->render('add', array(
            'collection' => $collection
        ));
	}
	
	/**
	 * Редактирование подборки
	 * @param int $id
	 * @return 
	 */
	public function actionEdit($id){
        if(Yii::app()->user->isGuest)
            $this->redirect(array('user/login'));
		$collection = Collection::model()->findByPk($id);
        if(!$collection)
            throw new CHttpException(404);
		if($collection->user_id!=Yii::app()->user->id)
            throw new CHttpException(404);
	
		if(isset($_POST['fav_del'])){
			if($collection->delete())
                $this->redirect(array('user/index', 'user'=> Yii::app()->user->name));
			else
                $this->refresh();
		}
		
        if(isset($_POST['Collection'])){
            $collection->attributes = $_POST['Collection'];
            if($collection->save())
                $this->redirect(array('user/index', 'user'=> Yii::app()->user->name));
        }
        $this->render('edit', array(
            'collection' => $collection
        ));
    }
	
	/**
	 * Просмотр подборки
	 * @param int $id
	 * @return 
	 */
	public function actionIndex($id){
		$collection = Collection::model()->findByPk($id);
		if(!$collection)
            throw new CHttpException(404);
		$this->render('view', array(
            'collection' => $collection
        ));
	}
	
	/**
	 * RSS подборки
	 * @param int $id
	 * @return 
	 */
	public function actionRss($id){
		$collection = Collection::model()->findByPk($id);
        if(!$collection)
            throw new CHttpException(404);
		header("content-type: application/rss+xml");
        $this->render('rss', array(
            'collection' => $collection
        ));
		exit();
	}
}