<?php

class AdminController extends Controller
{
    protected function beforeAction(CAction $action){
        if(Yii::app()->user->isGuest){
        	throw new CHttpException(404);
        }
		
        $is_admin = Users::model()->findByPk(Yii::app()->user->id, "role=:role", array('role'=>'admin'));
        $is_admin = Yii::app()->user->getState('admin');
        if(!$is_admin){
            throw new CHttpException(404);
        }
        return true;
    }
	
    /**
     * Главная страница
     * @return 
     */
	public function actionIndex()
	{
	    Yii::app()->params['title'] = 'Добро пожаловать в админку — ' . Yii::app()->params['title'];
		$this->render('index');
	}
	
    /**
     * Просмотр новых сайтов
     * @return 
     */
    public function actionNew(){
    	$data = new CActiveDataProvider(
        'Links',
		array(
		'criteria'=>array(
            'condition'=>'visible = :visible',
			'params' => array(':visible' => 0),
            'order'=>'t.id ASC',
			'with' => 'category'
        )));
		
        Yii::app()->params['title'] = 'Управление новыми сайтами — ' . Yii::app()->params['title'];
        $this->render('new_links', array('data' => $data));
    }
	
    /**
     * Просмотр всех категорий
     * @return 
     */
    public function actionAll(){
        Yii::app()->params['title'] = 'Управление категориями — ' . Yii::app()->params['title'];
        $this->render('category_all', array(
          'categories'  => Category::model()->with('linksCount')->findAll(array('order' =>'catname ASC'))
        ));
    }
	
    /**
     * Добавление категории
     * @return 
     */
    public function actionCatadd(){
		$form = new Category('add');
		if(isset($_POST['Category'])){
			$form->attributes = $_POST['Category'];
			if($form->save()) 
                $this->redirect(array('admin/all'));
        }

        Yii::app()->params['title'] = 'Добавление категории — ' . Yii::app()->params['title'];
        $this->render('category_add', array(
			'category'      => $form
        ));
    }
	
	/**
	 * Удаление категории
	 * @param int $id [optional] - id категории
	 * @return 
	 */
	public function actionCatdelete($id = false){
	    if(!Yii::app()->request->isPostRequest){
            Yii::app()->end();
        }

		$category = Category::model()->findByPk($id);
		if(!$category)
            throw new CHttpException(404);
		if($category->linksCountAll<1){
			$category->delete();
		}
        $this->redirect(array('admin/all'));
	}
	
	/**
	 * Редактирование категории
	 * @param int $id [optional] - id категории
	 * @return 
	 */
	public function actionCatedit($id = false){
		$category = Category::model()->findByPk($id);
		if(!$category){
			throw new CHttpException(404);
		}
		if(isset($_POST['Category'])){
		    $category->attributes = $_POST['Category'];
            if($category->save()){
                $this->redirect(array('admin/all'));
            }
		}

        Yii::app()->params['title'] = 'Редактирование категории — ' . Yii::app()->params['title'];
        $this->render('category_edit', array(
            'category'      => $category
        ));
    }
    
    public function actionComments($page = 1){
        $page = intval($page);
        $page = ($page > 0) ? $page : 1;
        
        $count = Comments::model()->count();
        $pages = new CPagination($count);
        // элементов на страницу
        $pages->pageSize = 20;
        $pages->route = 'admin/comments';
        
        $comments_paginator = $this->widget(
            'CLinkPager',
            array_merge(Yii::app()->params['link_pager'], array('pages' => $pages)),
            true
         );
        
        $comments = Comments::model()->findAll(array(
            'order'     => 'id DESC',
            'offset'    => ($page-1)*$pages->pageSize,
            'limit'     => $pages->pageSize
            
        ));
        Yii::app()->params['title'] =  'Комментарии' . ' — ' . Yii::app()->params['title'];
        $this->render('comments', array(
            'comments'             =>$comments,
            'comments_paginator'     =>$comments_paginator,
            'page'              => $page
        ));
    }
}