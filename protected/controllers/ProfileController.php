<?php

class ProfileController extends Controller
{
    public function actions(){
        return array(
            'captcha'=>array(
                'class'=>'CCaptchaAction',
            ),
        );
    }
    /**
     * /profile
     * @return 
     */
    public function actionIndex(){
        if(Yii::app()->user->isGuest){
            throw new CHttpException(404);
        }
		// Простой редирект в профиль пользователя /user/{username}
		$this->redirect(array('user/index', 'user'=> Yii::app()->user->name));
    }
    
	/**
	 * Смена email
	 * @param string $hash [optional]
	 * @return 
	 */
    public function actionEmail($hash = false){
        if(Yii::app()->user->isGuest)
            $this->redirect(array('user/login'));
        $errors = array();
        $user = Users::model()->findByPk(Yii::app()->user->id);
        $email = '';
        if($hash){
            $hash = trim($hash);
            $row = Yii::app()->db->createCommand(array(
                'from' => 'email_change',
                'where' => 'hash=:hash AND userid=:userid',
                'params' => array(':hash'=>$hash, ":userid" => Yii::app()->user->id),
            ))->queryRow();
            if($row){
                $user->email = $row['email'];
                if($user->save(true, array('email'))){
                    Yii::app()->db->createCommand()->delete('email_change', 'userid=:userid', array(':userid' => Yii::app()->user->id));
                    $this->redirect(array('profile/email'));
                }
            }
            else
                throw new CHttpException(404);
        }

        if(isset($_POST['email'])){
            $email = trim($_POST['email']);
            $v = new CEmailValidator();
            if($v->validateValue($email)){
                $hash = md5(md5(time()).md5($email). md5(Yii::app()->user->name));
                $command = Yii::app()->db->createCommand();
                $res = $command->insert('email_change', array(
                    'userid'    => Yii::app()->user->id,
                    'email'     => $email,
                    'ip'        => @$_SERVER['REMOTE_ADDR'],
                    'hash'      => $hash                    
                ));
                if($res){
                	MailHelper::sendEmailChangeLink($user, $email, $hash);
					Yii::app()->session['changed'] = true;
					$this->refresh();
                }
                else{
                    $errors[] = 'В данный момент смена email невозможна. Попробуйте позже.';    
                }
            }
            else
                $errors[] = 'Указанный email имеет неверный формат';
        }
        $changed = isset(Yii::app()->session['changed']) ? true : false;
        unset(Yii::app()->session['changed']);
        Yii::app()->params['title'] =  'Смена email' . ' — ' . Yii::app()->params['title'];
        
        $this->render('email', array(
            'user'      => $user,
            'email'     => $email,
            'errors'    => $errors,
            'changed'   => $changed
        ));
    }
    /**
     * Смена пароля
     * @return 
     */
    public function actionPass(){
        if(Yii::app()->user->isGuest)
            $this->redirect(array('user/login'));
        $errors = array();
        $user = Users::model()->findByPk(Yii::app()->user->id);
        $oldpass = $pass = $pass2 = '';
        if(isset($_POST['oldpass']) && isset($_POST['pass']) && isset($_POST['pass2'])){
            do{
                $oldpass = strval($_POST['oldpass']);
                $pass = trim(strval($_POST['pass']));
                $pass2 = trim(strval($_POST['pass2']));
                if(md5($oldpass)!==$user->password){
                    $errors[] = 'Текущий пароль указан неверно';
                    break;
                }
                if($pass!==$pass2){
                    $errors[] = 'Новый пароль и его подтверждение не совпадают';
                    break;
                }
                $user->password = $pass;
                if($user->validate(array('password')) && $user->save(false, array('password'))){
                    Yii::app()->session['changed'] = true;
                    $this->redirect('/user/pass/', true, 302);
                }
                else{
                    // Ошибка валидации
                    if($user->hasErrors())
                    foreach($user->getErrors() as $er)
                        foreach($er as $error){
                            $errors[] = $error;
                        }
                }
                
            }
            while(false);
        }

        $changed = isset(Yii::app()->session['changed']) ? true : false;
        unset(Yii::app()->session['changed']);
        Yii::app()->params['title'] =  'Смена пароля' . ' — ' . Yii::app()->params['title'];
        
        $this->render('pass', array(
            'user'      => $user,
            'errors'    => $errors,
            'changed'   => $changed
        ));
    }
	
	public function actionEdit(){
		if(Yii::app()->user->isGuest)
            $this->redirect(array('user/login'));
		$errors = array();
		$profile = Profile::model()->findByPk(Yii::app()->user->id);
		// Автосоздание профиля
		if($profile===null){
			$profile = new Profile();
			$profile->user_id = Yii::app()->user->id;
			if($profile->save())
                $this->refresh();
			else
                throw new CHttpException(500);
		}
		// Обновление аватара
		if(isset($_POST['avatar_update']) && isset($_FILES['avatar'])){
			$profile->_avatar = CUploadedFile::getInstanceByName('avatar');
			if($profile->_avatar && $profile->validate(array('_avatar'))){
				$filename = 'user' . $profile->user_id .'_'. time() . '.' . $profile->_avatar->extensionName;
				$dir = Yii::app()->params->AVATARS_DIR;
				if($profile->_avatar->saveAs($dir . $filename)){
					/**/
					require_once Yii::app()->basePath . '/vendors/phmagick/phmagick.php';
                    $avatar = new phMagick($dir . $filename);
					$avatar->setImageQuality(90);
                    $avatar->setDestination($dir . $filename);
                    $avatar->resizeExactly(100,100);
					//$avatar->crop(100,100); // need?
					$avatar->setDestination($dir . 't_'. $filename);
					$avatar->resize(50);
					/**/
					/*
					require_once Yii::app()->basePath . '/vendors/PhpThumb/ThumbLib.inc.php';
					$thumb = PhpThumbFactory::create($dir . $filename);
                    $thumb->setOptions( array('jpegQuality' => 90) );
                    $thumb->resize(100);
                    $thumb->crop(0, 0, 100, 100);
                    $thumb->save($dir . $filename);
                    $thumb->resize(50);
                    $thumb->save($dir . 't_'. $filename);
					*/
                    if ($profile->avatar && $filename!=$profile->avatar) {
                        @unlink($dir . $profile->avatar);
						@unlink($dir . 't_'.$profile->avatar);
                    }
					$profile->avatar = $filename;
					if($profile->save(false, array('avatar'))){
						$this->redirect(array('user/index', 'user'=> Yii::app()->user->name));
					}
				}
			}
			else
                foreach($profile->getErrors() as $er)
                    foreach($er as $error)
                        $errors[] = $error;
		}
		// Удаление аватара
		if(isset($_POST['avatar_delete'])){
			if ($profile->avatar && $profile->avatar) {
                @unlink(Yii::app()->params->AVATARS_DIR . $profile->avatar);
				@unlink(Yii::app()->params->AVATARS_DIR . 't_'.$profile->avatar);
            }
			$profile->avatar = '';
            if($profile->save(false, array('avatar'))){
                $this->redirect(array('user/index', 'user'=> Yii::app()->user->name));
            }
		}
		// Сохранение изменений
		if(isset($_POST['Profile'])){
			$profile->attributes = $_POST['Profile'];
			if($profile->save())
                $this->redirect(array('user/index', 'user'=> Yii::app()->user->name));
			else
                foreach($profile->getErrors() as $er)
                    foreach($er as $error)
                        $errors[] = $error;
                        
		}
		Yii::app()->params['title'] =  'Редактирование профиля — ' . Yii::app()->params['title'];
		$this->render('edit', array(
            'profile'      => $profile,
            'errors'    => $errors,
        ));
		
		
		
	}
}