<?php

class UserController extends Controller
{
    public function actions(){
        return array(
            'captcha'=>array(
                'class'=>'CCaptchaAction',
            ),
        );
    }
    /**
     * Разлогин
     * @return null
     */
	public function actionLogout(){
		if( isset($_POST['logout']) &&
            !Yii::app()->user->isGuest)
        {
            Yii::app()->user->logout();	
		}
		$this->redirect(Yii::app()->request->urlReferrer?Yii::app()->request->urlReferrer:Yii::app()->user->returnUrl);
	}
	/**
	 * Авторизация
	 * @return null
	 */
    public function actionLogin(){
    	if(!Yii::app()->user->isGuest){
    		// Уже авторизованы
    		$this->redirect('/', true, 302);
    	}
        $form = new Users('login');
		$errors = array();
		if(isset($_POST['Users'])){
		    $form->attributes = $_POST['Users'];
            if($form->validate()){
            	if(isset($_POST['return']))
				    $this->redirect($_POST['return']);
				else
                    $this->redirect('/', true, 302);
            }
        }
        Yii::app()->params['title'] =  'Вход на сайт' . ' — ' . Yii::app()->params['title'];
        $this->render('login', array(
            'form'      => $form,
            'errors'    => $errors,
			'return'     => isset($_POST['return'])?$_POST['return']:Yii::app()->request->urlReferrer
        ));
    }
    /**
     * Регистрация
     * @return null
     */
    public function actionRegister(){

        if(!Yii::app()->user->isGuest){
            $this->redirect(Yii::app()->getBaseUrl(true));
        }

        $form = new Users('register');
        if(isset($_POST['Users'])){
            $form->attributes = $_POST['Users'];
            if($form->save()){
            	Yii::app()->user->setFlash('register', true);
                $this->refresh;
            }
        }
        
        Yii::app()->params['title'] =  'Регистрация' . ' — ' . Yii::app()->params['title'];
        $this->render('register', array(
            'form'      => $form
        ));
    }
	
	/**
	 * Просмотр профиля юзера
	 * @param string user [optional]
	 * @return 
	 */
	public function actionIndex($user = FALSE){
		if(!$user && Yii::app()->user->isGuest){
			throw new CHttpException(404);
		}
		
		$username = $user ? $user : Yii::app()->user->name;
		
		$user = Users::model()->findByAttributes(array('username'=>$username));
		if($user===NULL){
			throw new CHttpException(404);
		}
		$owner = $user->userID===Yii::app()->user->id;
		
		$collections = Collection::model()->with('linksCount')->findAllByAttributes(array(
            'user_id' => $user->userID
		));
		
		$comments = Comments::model()->with('links')->findAllByAttributes(array(
            'userid' => $user->userID				
		),
		array(
		  'order' => 't.id desc'
		));
		
		Yii::app()->params['title'] =  'Профиль пользователя "' . $user->username . '" — ' . Yii::app()->params['title'];
        $this->render('profile', array(
            'user'      => $user,
            'owner'     => $owner,
			'comments' => $comments,
			'collections' => $collections
        ));
	}
	
	public function actionOpenid(){
		$err = false;
		$user_openid = Yii::app()->session['user_openid'];
		$loid = Yii::app()->loid->load();
        if (!empty($_GET['openid_mode'])) {
		    if ($_GET['openid_mode'] == 'cancel') {
		        $err = Yii::t('core', 'Authorization cancelled');
		    } 
			else {
		        try {
		        	if($loid->validate()){
						$userinfo = $loid->getAttributes();
						$user_openid = array();
						$user_openid['identity'] = $loid->identity;
						if(isset($userinfo['contact/email'])){
							$user_openid['email'] = $userinfo['contact/email'];
						}
						if(isset($userinfo['namePerson/friendly'])){
                            $user_openid['username'] = $userinfo['namePerson/friendly'];
                        }
						
						if(Users::authenticateByEmail(@$user_openid['email'])){
							$this->redirect(array('main/index'));
						}
						elseif(Users::authenticateByOidIdentity($user_openid['identity'])){
							$this->redirect(array('main/index'));
						}

						Yii::app()->session['user_openid'] = $user_openid;
						$this->redirect(array('user/openid'));
		        	}
					else{
						
					}
		        } catch (Exception $e) {
		            $err = Yii::t('core', $e->getMessage());
		        }
		    }
		} elseif(isset($_GET['openid_identifier'])) {
			$oauth_providers = array('http://facebook.com/' => $this->createUrl('user/oauthfacebook'));
		    $loid->identity = strval($_GET['openid_identifier']);
			if(key_exists($loid->identity, $oauth_providers)){
				$this->redirect($oauth_providers[$loid->identity]);
			}
			
		    $loid->required = array('namePerson/friendly', 'contact/email', 'namePerson');
		    $loid->realm     = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; 
		    $loid->returnUrl = $loid->realm . $_SERVER['REQUEST_URI'];
		    if (empty($err)) {
		        try {
		            $url = $loid->authUrl();
		            $this->redirect($url);
		        } catch (Exception $e) {
		            $err = Yii::t('core', $e->getMessage());
		        }
		    }
		}
		
		$login_form = new Users('login');
		if(isset($_POST['login'], $_POST['Users'])){
            $login_form->attributes = $_POST['Users'];
			if($login_form->validate() && $user_openid['identity']){
				if($login_form->attachOidIdentity($user_openid['identity'], Yii::app()->user->id)){
					Yii::app()->session->offsetUnset('user_openid');
					$this->redirect(array('main/index'));
				}
			}
		}
		$reg_form = new Users('register_oid');

		$reg_form->username = @$user_openid['username'];
		$reg_form->email = @$user_openid['email'];
		if(isset($_POST['create'], $_POST['Users'])){
            $reg_form->attributes = $_POST['Users'];
			$pass = md5(time().mt_rand(0, 1).$reg_form->attributes['username']);
			$reg_form->password = $reg_form->password2 = $pass;
			$reg_form->validate();
			if(@$user_openid['identity'] && $reg_form->save()){
				if(Users::authenticateById($reg_form->userID)){
				    $reg_form->attachOidIdentity($user_openid['identity'], $reg_form->userID);
					Yii::app()->session->offsetUnset('user_openid');
					$this->redirect(array('/user'));	
				}
			}
        }
		
		$this->render('openid', array(
            'userinfo' => $user_openid,
			'error' => $err,
			'login_form' => $login_form,
			'reg_form' => $reg_form,
		));
	}
	
	public function actionOauthFacebook(){
		Yii::import('application.vendors.facebook.src.*');
		$facebook = new Facebook(array(
            'appId' => Yii::app()->params['facebook']['app_id'], 
			'secret' => Yii::app()->params['facebook']['secret']));
		$user = $facebook->getUser();
		if($user){
			try {
                $userinfo = $facebook->api('/me');
				$user_oauth= array();
                $user_oauth['identity'] = $userinfo['link'];
                if(isset($userinfo['email'])){
                    $user_oauth['email'] = $userinfo['email'];
                }
                if(isset($userinfo['username'])){
                    $user_oauth['username'] = $userinfo['username'];
                }
                
				if(Users::authenticateByEmail(@$user_oauth['email'])){
                    $this->redirect(array('main/index'));
                }
                elseif(Users::authenticateByOidIdentity($user_oauth['identity'])){
                    $this->redirect(array('main/index'));
                }

                Yii::app()->session['user_openid'] = $user_oauth;
                $this->redirect(array('user/openid'));
            }
            catch(FacebookApiException $e) {
            	//echo $e;
                //error_log($e);
                //$user = null;
				$this->redirect(array('user/openid'));
            }
		}
		else{
			if(isset($_GET['error_reason'])){
				$this->redirect(array('user/openid'));
			}
			$loginUrl = $facebook->getLoginUrl(array('scope' => 'email,user_about_me'));
			$this->redirect($loginUrl);
		}
	}

    
}