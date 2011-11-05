<?php

class AjaxController extends Controller
{
	
	public function actions(){
        return array(
            'captcha'=>array(
                'class'=>'CCaptchaAction',
                'minLength' =>7,
                'testLimit' => 2,
                //'fontFile'=> Yii::app()->basePath . '/fonts/TRITTO__.TTF'
            ),
        );
    }
	
	protected function beforeAction(CAction $action){
		if($action->id == 'captcha') return true;
	    if(!(Yii::app()->request->isAjaxRequest)){
            exit();
        }
        return parent::beforeAction($action);
    }
	/**
	 * Определение PR сайта
	 * @return 
	 */
    public function actionGoogle() {
		$res = array('result' => false);
        if(isset($_POST['link_id']) && ($link = Links::model()->findByPk($_POST['link_id']))){
            if($link){
                if(($res['value'] = GooglePageRank::getPageRank($link->url))!== false){
                    $res['value'] = intval($res['value']);
                    $res['result'] = true;
                    $link->pr = $res['value'];
                    $link->pr_lastdate = time();
                    $link->save(true, array('pr', 'pr_lastdate'));
                }
            }
        }
        header('Content-type: application/json;');
        exit(json_encode($res));
    }
	/**
	 * Получение ТИЦ сайта
	 * @return 
	 */
	public function actionYandex(){
		$res = array('result' => false);
        if(isset($_POST['link_id']) && ($link = Links::model()->findByPk($_POST['link_id']))){
            if($link){
                if(($res['value'] = YandexCy::getCy($link->url))!==false){
                    $res['value'] = intval($res['value']);
                    $res['result'] = true;
                    $link->ci = $res['value'];
                    $link->ci_lastdate = time();
                    $link->save(true, array('ci', 'ci_lastdate'));
                }
            }
		}
        header('Content-type: application/json;');
        exit(json_encode($res));
    }
	/**
	 * Голосование за сайт
	 * @return 
	 */
	public function actionVote(){
	    $r = array('rate'=>false, 'votes'=>false);
        if(isset($_POST['link_id']) && isset($_POST['vote'])){
            if(!empty($_COOKIE) && Votes::canVote($_POST['link_id'])){
                $res = Votes::addVote($_POST['link_id'], $_POST['vote']);
                if($res){
                    $r = $res;
                }
            }
        }
        header('Content-type: application/json;');
        exit(json_encode($r));
	}
	/**
	 * Создание скриншота (доступно только администратору)
	 * @param int $id [optional]
	 * @return 
	 */
    public function actionScreenshot($id = false){
        $res = array('result'=>false);
		
        if(!$id || !Yii::app()->user->isAdmin())
            exit();
        $link = Links::getLink($id, false);
        if(!$link){
            exit();
        }
        $orig_foto = $link->foto;
        $f = str_replace('/','',$link->domain) . date("_Y.m.d-H.i.s", time()).".jpg";
        // TODO зачистить от говнокода
        $tmpfname = Yii::app()->cutycapt->capture($link->url);
        if (file_exists($tmpfname)) {
            if (filesize($tmpfname) > 1) {
                require_once Yii::app()->basePath . '/vendors/phmagick/phmagick.php';
                $thumb = new phMagick($tmpfname);
                $thumb->setImageQuality(90);
                // Основной скрин (800 х 600)
                $thumb->setDestination(Yii::app()->params->IMAGES_DIR.$f);
                $thumb->resize(800)->crop(800,600,0,0,'North');
                // Эскиз (200 х 150)
                $thumb->setDestination(Yii::app()->params->IMAGES_DIR.'t_'.$f);
                $thumb->resize(200);//->crop(200,150,0,0,'North');
                if ($orig_foto && $orig_foto!=$f) {
                    unlink(Yii::app()->params->IMAGES_DIR.$orig_foto);
                    unlink(Yii::app()->params->IMAGES_DIR.'t_'.$orig_foto);
                }
                $link->foto = $f;
                if($link->save(true, array('foto'))){
                    $res = array('result'=>true);   
                }
            }
            unlink($tmpfname);
        }
        header('Content-type: application/json;');
        exit(json_encode($res));
    }
    
	public function actionScreenshotGD($id = false){
		$res = array('result'=>false);

        if(!$id || !Yii::app()->user->isAdmin()){
            exit();
        }
        $link = Links::getLink($id, false);
        if(!$link){
            exit();
        }
		$orig_foto = $link->foto;
        $f = str_replace('/','',$link->domain) . date("_Y.m.d-h.i.s", time()).".jpg";
        // TODO зачистить от говнокода
        $tmpfname = Yii::app()->cutycapt->capture($link->url);
        if (file_exists($tmpfname)) {
            if (filesize($tmpfname) > 1) {
                if (copy($tmpfname, Yii::app()->params->IMAGES_DIR . $f)) {
                    if (file_exists(Yii::app()->params->IMAGES_DIR . $f)) {
                        require_once Yii::app()->basePath . '/vendors/PhpThumb/ThumbLib.inc.php';
                        try{
                            $thumb = PhpThumbFactory::create(Yii::app()->params->IMAGES_DIR.$f);
                            $thumb->setOptions( array('jpegQuality' => 90) );
                            $thumb->resize(800);
                            $thumb->crop(0, 0, 800, 800);
                            $thumb->save(Yii::app()->params->IMAGES_DIR.$f);
                            $thumb->resize(200)->crop(0,0,200,150);
                            $thumb->save(Yii::app()->params->IMAGES_DIR.'t_'.$f);
                        }
                        catch(exception $e) {
                            //exit();
                        }
                        if ($orig_foto && $orig_foto!=$f) {
                            unlink(Yii::app()->params->IMAGES_DIR.$orig_foto);
                            unlink(Yii::app()->params->IMAGES_DIR.'t_'.$orig_foto);
                        }
						$link->foto = $f;
						if($link->save(true, array('foto'))){
                            $res = array('result'=>true);	
						}
                    }
                }
            }
            unlink($tmpfname);
        }
		header('Content-type: application/json;');
        exit(json_encode($res));
	}
    
	/**
	 * Feedback
	 * @return 
	 */
    public function actionFeedback(){
        $result = array('error'=>true, 'message'=>'');
		$model = new FeedbackForm();
		if(isset($_POST['FeedbackForm'])){
            $model->attributes = $_POST['FeedbackForm'];
			if($model->validate()){
				if(MailHelper::sendFeedback($model->email, $model->text))
                    $result['error'] = false;
                else
                    $result['message'] = 'Ошибка отправки сообщения на e-mail';
			}	
			else{
				$result['message'] = array_pop(array_pop(array_values($model->errors)));
			}
		}
		header('Content-type: application/json;');
        exit(json_encode($result));
    }
	
	public function actionSaveSiteCollection(){
		if(Yii::app()->user->isGuest)
            exit();
		$link_id = @$_POST['link_id'];
		$link = Links::model()->findByPk($link_id);
        if(!$link) exit();
		$collections = @$_POST['collections'];
		if(!is_array($collections)) $collections = false;
		else{
			foreach($collections as $k=>$v){
				$collections[$k] = intval($v);
			}
			$collections = array_unique($collections);
		}
		$new_collection = false;
		if(isset($_POST['new_collection']) && $_POST['new_collection']=='1'){
			$new_collection = trim(@$_POST['new_collection_name']);
			if(!$new_collection) $new_collection = false;
		}
		$list = array();
		if($collections) $list = array_merge($list, $collections);
		if($new_collection){
			$c = new Collection();
			$c->title = trim($new_collection);
			$c->user_id = Yii::app()->user->id;
			if($c->save())
                $list[] = $c->id;
		}
        $link->setCollections($list);
		$html = $this->render('//collection/ajax/info_site_collections', array(
            'link'      => $link,
        ), true);
        exit($html);
	}
	
	public function actionGetEditSiteCollectionPanel(){
		if(Yii::app()->user->isGuest)
            exit();
		$link_id = @$_POST['link_id'];
		$link = Links::model()->findByPk($link_id);
		if(!$link) exit();
		$link_collection = array();
		foreach($link->collections as $c){
			$link_collection[] = $c->id;
		}

		$user = Users::model()->findByPk(Yii::app()->user->id);
		
		$html = $this->render('//collection/ajax/edit_site_collections', array(
            'link'      => $link,
			'link_collection' => $link_collection,
            'user' => (Yii::app()->user->isGuest) ? false : Users::model()->findByPk(Yii::app()->user->id)
        ), true);
		exit($html);
	}
	
	public function actionGetInfoSiteCollectionPanel(){
        if(Yii::app()->user->isGuest)
            exit();
        $link_id = @$_POST['link_id'];
        $link = Links::model()->findByPk($link_id);
        if(!$link) exit();

        $html = $this->render('//collection/ajax/info_site_collections', array(
            'link'      => $link,
        ), true);
        exit($html);
    }
	
	public function actionDeleteSiteFromCollection(){
		if(Yii::app()->user->isGuest)
            exit();
		$link_id = @$_POST['link_id'];
		$collection_id = @$_POST['collection_id'];
		if(!$link_id || !$collection_id) exit();
		$command = Yii::app()->db->createCommand();
		$res = $command->delete(
            'links_favs', 
			'fav_id = :fav_id AND link_id = :link_id AND user_id = :user_id', 
			array(
                ':fav_id'=>$collection_id,
				':link_id' => $link_id,
				':user_id' => Yii::app()->user->id
			)
		);
		if($res) exit('true');
		exit();
	}
	
	public function actionSendBrokenSite(){
		$link_id = @$_POST['link_id'];
        $link = Links::model()->findByPk($link_id);
        if(!$link) exit();
		$link->broken = true;
		if(!$link->save(false, array('broken')))
            exit();
		exit('true');
	}
	
	public function actionCommentAdd(){
		$link_id = @$_POST['link_id'];
        $link = Links::model()->findByPk($link_id);
        if(!$link) exit();
		$result = array('error'=>true, 'message'=>'');
		if(isset($_POST['Comment'])){
			$comment = new Comments('add');
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
            	$comment = Comments::model()->findByPk($comment->id);
                $result['error'] = false;
				$html = $this->render('//layouts/comment', array(
                    'link'      => $link,
					'c' => $comment
                ), true);
				$result['message'] = $html;
            }
            else{
            	$errors = array();
                if($comment->hasErrors())
                foreach($comment->getErrors() as $er)
                    foreach($er as $error){
                        $errors[] = $error;
                    }
				$errors = $this->render('//layouts/messages', array(
                    'errors'      => $errors
                ), true);
				$result['message'] = $errors;
            }
        }
		header('Content-type: application/json;');
        exit(json_encode($result));
	}
	
	public function actionTagsAutocomplete(){
		if(empty($_GET)) die();
		$limit = Yii::app()->request->getQuery('limit', 10);
		$query = Yii::app()->request->getQuery('q', null);
		if(is_null($query)) exit();
		$query = trim(strval($query));
		if(strlen($query) < 3) exit();
		$criteria = new CDbCriteria();
        $criteria->addSearchCondition('name', $query);
		$criteria->limit = 10;
		$tags = Tag::model()->findAll($criteria);
		$result = array();
		if($tags){
			foreach($tags as $tag)
                $result[] = $tag->name;
		}
		exit(implode("\n", $result));
	}
}