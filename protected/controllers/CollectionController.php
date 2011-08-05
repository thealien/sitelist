<?php

class CollectionController extends Controller
{
	
	public function actionAdd(){
		if(Yii::app()->user->isGuest)
            $this->redirect(array('user/login'));
		$errors = array();
		$collection = new Collection();
		// Сохранение изменений
        if(isset($_POST['Collection'])){
            $collection->attributes = $_POST['Collection'];
			$collection->user_id = Yii::app()->user->id;
            if($collection->save())
                $this->redirect(array('user/index', 'user'=> Yii::app()->user->name));
            else
                foreach($collection->getErrors() as $er)
                    foreach($er as $error)
                        $errors[] = $error;
                        
        }
		$this->render('add', array(
            'collection' => $collection,
			'errors' => $errors
        ));
	}
	
	public function actionEdit($id){
        if(Yii::app()->user->isGuest)
            $this->redirect(array('user/login'));
		$collection = Collection::model()->findByPk($id);
        if(!$collection)
            throw new CHttpException(404);
		if($collection->user_id!=Yii::app()->user->id)
            throw new CHttpException(404);
        $errors = array();
		
        // Удаление		
		if(isset($_POST['fav_del'])){
			if($collection->delete())
                $this->redirect(array('user/index', 'user'=> Yii::app()->user->name));
			else
                $this->refresh();
		}
		
        // Сохранение изменений
        if(isset($_POST['Collection'])){
            $collection->attributes = $_POST['Collection'];
            if($collection->save())
                $this->redirect(array('user/index', 'user'=> Yii::app()->user->name));
            else
                foreach($collection->getErrors() as $er)
                    foreach($er as $error)
                        $errors[] = $error;
        }
        $this->render('edit', array(
            'collection' => $collection,
            'errors' => $errors
        ));
    }
	
	public function actionIndex($id){
		$collection = Collection::model()->findByPk($id);
		if(!$collection)
            throw new CHttpException(404);
		$user = Users::model()->findByPk($collection->user_id);
		$this->render('view', array(
            'collection' => $collection,
            'user' => $user
        ));
	}
	
	public function actionRss($id){
		$collection = Collection::model()->findByPk($id);
        if(!$collection)
            throw new CHttpException(404);
        $user = Users::model()->findByPk($collection->user_id);
		header("content-type: application/rss+xml");
        $this->render('rss', array(
            'collection' => $collection,
            'user' => $user
        ));
		exit();
	}
}