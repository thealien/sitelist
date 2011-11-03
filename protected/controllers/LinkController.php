<?php

class LinkController extends Controller
{

    public function actions(){
        return array(
            'captcha'=>array(
                'class'=>'CCaptchaAction',
				'minLength' =>7,
                'testLimit' => 2
            ),
        );
    }
    /**
     * Удаление сайта
     * @param int $id [optional] ID сайта
     * @return
     */
	public function actionDelete($id = false){
	    if(!Yii::app()->request->isPostRequest){
	        throw new CHttpException(404);
	    }
		$admin = Yii::app()->user->getState('admin') ? true : false;
		if(!$id || !$admin){
            throw new CHttpException(404);
        }
        $link = Links::getLink($id, false);
        if(!$link){
            throw new CHttpException(404);
        }
        $link->delete();
        Votes::model()->deleteAllByAttributes(array('link_id'=>$id));
        Comments::model()->deleteAllByAttributes(array('linkid'=>$id));
        $this->redirect('/');
	}
	/**
	 * Редактирование сайта
	 * @param int $id [optional]
	 * @return 
	 */
	public function actionEdit($id = false){
		if(!$id){
            throw new CHttpException(404);
		}
        $admin = Yii::app()->user->getState('admin') ? true : false;
        if(!$admin){
            throw new CHttpException(404);
        }
		$linkModel = Links::model();
		$linkModel->setScenario('edit');
		$link = $linkModel->findByPk($id);
        if(!$link){
            throw new CHttpException(404);
        }
		if(isset($_POST['Links'])){
			$link->setScenario('edit');
			$link->attributes = $_POST['Links'];
			if($link->validate()){
				// Если нужно удалить фото
				if(isset($_POST['deleteFoto']) && ($_POST['deleteFoto']) && $link->foto!=''){
                    @unlink(Yii::app()->params->IMAGES_DIR . $link->foto);
                    @unlink(Yii::app()->params->IMAGES_DIR . 't_' . $link->foto);
                    $link->foto = '';
                }
				$link->save(false);
				$this->redirect(array('link/view', 'id' => $link->id));
			}
        }else{
        	$link->tags = $link->taggable->toString();
        }
		
        Yii::app()->params['title'] = 'Редактирование сайта — ' . Yii::app()->params['title'];
        $this->render('edit', array(
            'form'          => $link,
            'categories'    => Category::getList(),
        ));
    }
	/**
	 * Просмотр страницы сайта
	 * @param int $id [optional]
	 * @return 
	 */
	public function actionView($id = false){
    	if($id === false){
            throw new CHttpException(404);
        }
        $id = intval($id);
        
		$admin = Yii::app()->user->getState('admin') ? true : false;
        
		$link = Links::getLink($id, false);
		
        if(!$link){
            throw new CHttpException(404);
        }
        $pr_is_old = $ci_is_old = $captcha = false;
		if($link->visible || $admin){
		    
		$t = time();
		$pr_is_old = ($t - $link->pr_lastdate)>Yii::app()->params['SITE_RATE_DATE_EXP'];
		$ci_is_old = ($t - $link->ci_lastdate)>Yii::app()->params['SITE_RATE_DATE_EXP'];
		$link->voted = !Votes::canVote($link->id);
        }
        
        $lastLinks = Links::getLastLinks(3);

        Yii::app()->params['title'] = $link->title . ' — ' . Yii::app()->params['title'];
		$this->render('view', array(
            'link'      => $link,
            'pr_is_old' => $pr_is_old,
            'ci_is_old' => $ci_is_old,
            'lastLinks' => $lastLinks,
        ));
    }
	/**
	 * Добавление сайта
	 * @return 
	 */
	public function actionAdd(){
		$form = new Links('add');
		if(isset($_POST['Links'])){
			$form->attributes = $_POST['Links'];
			$form->ip = $_SERVER['REMOTE_ADDR'];
			$form->date_ts = time();
            $form->date = new CDbExpression('NOW()');
			$form->userid = Yii::app()->user->isGuest ? 0 : Yii::app()->user->id;
			if($form->save()){
                Yii::app()->session['added'] = true;
                Yii::app()->session['added_id'] = $form->id;
                MailHelper::sendNewSiteNotify($form->title, $form->url);
                $this->refresh();
            }
		}
		// Заполнение полей через букмарклет
		elseif(isset($_GET['source']) && $_GET['source']=='bookmarklet'){
            $form->url      = isset($_GET['bookmarklet_url'])       ?(string)$_GET['bookmarklet_url']       :'http://';
            $form->title    = isset($_GET['bookmarklet_title'])     ?(string)$_GET['bookmarklet_title']     :false;
            $form->desc     = isset($_GET['bookmarklet_desc'])      ?(string)$_GET['bookmarklet_desc']      :false;
			$form->url = @iconv('utf-8//IGNORE', 'utf-8', $form->url);
			$form->title = @iconv('utf-8//IGNORE', 'utf-8', $form->title);
			$form->desc = @iconv('utf-8//IGNORE', 'utf-8', $form->desc);
        }
		
		// конец добавления
        
        $added = isset(Yii::app()->session['added']) ? true : false;
        $added_id = isset(Yii::app()->session['added_id']) ? Yii::app()->session['added_id'] : false;
		unset(Yii::app()->session['added']);
        unset(Yii::app()->session['added_id']);
		
		Yii::app()->params['title'] = 'Добавление сайта в каталог — ' . Yii::app()->params['title'];
        $lastLinks = Links::getLastLinks(3);
        $this->render('add', array(
            'lastLinks'     => $lastLinks,
            'categories'    => Category::getList(),
            'added'         => $added,
            'added_id'      => $added_id,
            'form'          => $form
        ));
    }
	
	private function getCaptcha($action = false){
		return $this->widget('CCaptcha', array(
            'showRefreshButton'=>false,
            'clickableImage'=>true,
			'captchaAction' => ($action ? $action : 'captcha'),
            'imageOptions'=>array(
                'alt'=>'проверочный код',
                'title'=>'Кликни по картинке, чтобы сменить код',
                'border'=>1,
                'width'=>'100px',
                'height'=>'40px'
            )
        ), true);
	}
}
