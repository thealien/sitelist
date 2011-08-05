<?php

class LinkController extends Controller
{

    public function actions(){
        return array(
            'captcha'=>array(
                'class'=>'CCaptchaAction',
				'minLength' =>7,
                'testLimit' => 2
				//'fontFile'=> Yii::app()->basePath . '/fonts/TRITTO__.TTF'
            ),
        );
    }
    /**
     * Действие по умолчанию
     * @return 
     */
    public function actionIndex(){
        throw new CHttpException(404);
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
		$link = Links::getLink($id, false );
        if(!$link){
            throw new CHttpException(404);
        }
		$errors = array();
		if(isset($_POST['Link'])){
			$link->attributes = $_POST['Link'];
			if($link->validate()){
				// Если нужно удалить фото
				if(isset($_POST['deleteFoto']) && ($_POST['deleteFoto']) && $link->foto!=''){
                    @unlink(Yii::app()->params->IMAGES_DIR . $link->foto);
                    @unlink(Yii::app()->params->IMAGES_DIR . 't_' . $link->foto);
                    $link->foto = '';
                }
				$link->save(false);
				$this->redirect('/link/' . $link->id, true, 302);
			}
			else{
                if($link->hasErrors())
                foreach($link->getErrors() as $er)
                    foreach($er as $error){
                        $errors[] = $error;
                    }	
			}
        }
		
        $categories = Category::getRootCats();
        Yii::app()->params['title'] = 'Редактирование сайта — ' . Yii::app()->params['title'];
        $this->render('edit', array(
            'link'      => $link->attributes,
            'categories'=> $categories,
            'errors'    => $errors
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
        $errors = array();
        $pr_is_old = $ci_is_old = $captcha = false;
		if($link->visible || $admin){
		    
		$comment = new Comments('add');
		// Добавление комментария
		if(isset($_POST['Comment'])){
		    $comment->attributes = $_POST['Comment'];
            $comment->linkid = $link->id;
            $comment->userid = 0;
            $comment->datetime = new CDbExpression('NOW()');
            $comment->ip = $_SERVER['REMOTE_ADDR'];
            if(!Yii::app()->user->isGuest){
                $comment->userid = Yii::app()->user->id;
                $comment->user = Yii::app()->user->name;
            }
            if($comment->save()){
                $this->redirect('/link/' . $link->id, true, 302);
            }
            else{
                if($comment->hasErrors())
                foreach($comment->getErrors() as $er)
                    foreach($er as $error){
                        $errors[] = $error;
                    }
            }
        }
		$t = time();
		$pr_is_old = ($t - $link->pr_lastdate)>Yii::app()->params['SITE_RATE_DATE_EXP'];
		$ci_is_old = ($t - $link->ci_lastdate)>Yii::app()->params['SITE_RATE_DATE_EXP'];
		$link->voted = !Votes::canVote($link->id);
        if(Yii::app()->user->isGuest){
            $captcha = $this->getCaptcha('ajax/captcha');
            Yii::app()->clientScript->scriptMap=array('jquery.min.js'=>false, 'jquery.js'=>false);
        }
        
        }
        
        $lastLinks = Links::getLastLinks(3);

        Yii::app()->params['title'] = $link->title . ' — ' . Yii::app()->params['title'];
		$this->render('view', array(
            'link'      => $link,
            'pr_is_old' => $pr_is_old,
            'ci_is_old' => $ci_is_old,
            'lastLinks' => $lastLinks,
            //'admin'     => $admin,
            'captcha'   => $captcha,
            'comment'   => isset($comment->attributes) ? $comment->attributes : false,
            'errors'    => $errors,
			'user' => (Yii::app()->user->isGuest) ? false : Users::model()->findByPk(Yii::app()->user->id)
        ));
    }
	/**
	 * Добавление сайта
	 * @return 
	 */
	public function actionAdd(){
    	$errors = array();
		$form = new Links('add');
		if(isset($_POST['Link'])){
			$form->attributes = $_POST['Link'];
			$form->ip = $_SERVER['REMOTE_ADDR'];
			$form->date_ts = time();
            $form->date = new CDbExpression('NOW()');
			$form->userid = Yii::app()->user->isGuest ? 0 : Yii::app()->user->id;
			if($form->save()){
                Yii::app()->session['added'] = true;
                Yii::app()->session['added_id'] = $form->id;
                self::notify($form->title, $form->url);
                $this->redirect('/add/', true, 302);
            }
            else{
            	if($form->hasErrors())
            	foreach($form->getErrors() as $k=>$er){
            		foreach($er as $error){
                        $errors[] = $error;
                    }
					if($k=='url' && !(empty($form->url))){
						$l = Links::model()->findByAttributes(array('url'=>$form->url));
						if($l){
							$errors[] = $l->title . ' <a href="/link/'.$l->id.'">уже есть на сайте</a>';
						}
					}
            	}
				    
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
		$captcha = false;
        if(Yii::app()->user->isGuest){
            $captcha = $this->getCaptcha();
			Yii::app()->clientScript->scriptMap=array('jquery.min.js'=>false, 'jquery.js'=>false);
        }
        
        $added = isset(Yii::app()->session['added']) ? true : false;
        $added_id = isset(Yii::app()->session['added_id']) ? Yii::app()->session['added_id'] : false;
		unset(Yii::app()->session['added']);
        unset(Yii::app()->session['added_id']);
		
		Yii::app()->params['title'] = 'Добавление сайта в каталог — ' . Yii::app()->params['title'];
        $lastLinks = Links::getLastLinks(3);
        $categories = Category::getRootCats();
        $this->render('add', array(
            'lastLinks'     => $lastLinks,
            'categories'    => $categories,
            'added'         => $added,
            'added_id'      => $added_id,
            'errors'        => $errors,
            'captcha'       => $captcha,
            'form'          => $form->attributes
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
	
    /**
     * Оповещение о новом добавлении сайта
     * @param object $name
     * @param object $url
     * @return 
     */
    static private function notify($name, $url){
        $to  = "megafon-don@mail.ru";
        $from = 'mail@sitelist.in';
        $subject = "SiteList.in New Site";
        $message = "
        Добавлен новый сайт: ".htmlspecialchars($name)." - ".htmlspecialchars($url)."<br>
        ---------<br>
        Browser: ".htmlspecialchars(@$_SERVER['HTTP_USER_AGENT'])."<br>
        IP: ".htmlspecialchars(@$_SERVER['REMOTE_ADDR']); 
        $headers  = "Content-type: text/html; charset=utf-8 \r\n"; 
        $headers .= "From: ". htmlspecialchars($from) ."\r\n"; 
        return mail($to, $subject, $message, $headers);
    }
}
