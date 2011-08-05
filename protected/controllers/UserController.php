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
    
}