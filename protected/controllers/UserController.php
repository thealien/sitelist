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
		if(isset($_POST['loginbtn'])){
		    $form->attributes = $_POST['User'];
            if($form->validate()){
            	if(isset($_POST['return']))
				    $this->redirect($_POST['return']);
				else
                    $this->redirect('/', true, 302);
            }
            else{
                $errors[] = 'Неверные логин или пароль';
            }
        }
        Yii::app()->params['title'] =  'Вход на сайт' . ' — ' . Yii::app()->params['title'];
        $this->render('login', array(
            'form'      => $form->attributes,
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
            // Уже авторизованы
            $this->redirect('/', true, 302);
        }
        $errors = array();
        $form = new Users('register');
        if(isset($_POST['regbtn'])){
            $form->attributes = $_POST['User'];
            if($form->save()){
                Yii::app()->session['register'] = true;
                $this->redirect('/register/', true, 302);
            }
            else{
                // Ошибка валидации
                if($form->hasErrors())
                foreach($form->getErrors() as $er)
                    foreach($er as $error){
                        $errors[] = $error;
                    }
            } 
        }
        
        $captcha = $this->widget('CCaptcha', array(
            'showRefreshButton'=>false,
            'clickableImage'=>true,
            'imageOptions'=>array(
                'alt'       => 'проверочный код',
                'title'     => 'Кликни по картинке, чтобы сменить код',
                'border'    => 1,
                'width'     => '100',
                'height'    => '40'
          )
        ), true);
        
        $register = isset(Yii::app()->session['register']) ? true : false;
        unset(Yii::app()->session['register']);
        Yii::app()->params['title'] =  'Регистрация' . ' — ' . Yii::app()->params['title'];
        $this->render('register', array(
            'errors'    => $errors,
            'captcha'   => $captcha,
            'register'  => $register,
            'form'      => $form->attributes
        ));
    }
	
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
						
						//var_dump($user_openid['identity']);
						//exit();
						
						
						Yii::app()->session['user_openid'] = $user_openid;
						// Попытаться авторизовать по e-mail
						// Попытаться авторизовать по identity
						
						$this->redirect(array('user/openid'));
		        	}
					else{
						
					}
		        } catch (Exception $e) {
		            $err = Yii::t('core', $e->getMessage());
		        }
		    }
		    if(!empty($err)) echo $err;
		} elseif(isset($_GET['openid_identifier'])) {
		    $loid->identity = strval($_GET['openid_identifier']);
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
		if(isset($_POST['User'])){
            $login_form->attributes = $_POST['User'];
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
		if(isset($_POST['NewUser'])){
            $reg_form->attributes = $_POST['NewUser'];
			$pass = md5(time().mt_rand(0, 1).$reg_form->attributes['username']);
			$reg_form->password = $reg_form->password2 = $pass;
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
			'reg_form' => $reg_form
		));
	}

    
}