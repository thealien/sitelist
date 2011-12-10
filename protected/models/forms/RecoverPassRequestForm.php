<?php

class RecoverPassRequestForm extends CFormModel {
    public $username;
    public $captcha = null;
    
    public function rules()
    {
        return array(
            array('username', 'required', 'message' => '{attribute} не может быть пустым'),
			array('username', 'exist', 'attributeName'=>'username', 'className'=>'Users', 'message' => 'Указанный {attribute} не существует'),
            array('captcha', 'captcha', 'allowEmpty'=>!extension_loaded('gd'), 'message' => 'Неверный код подтверждения'),
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'username'         => 'Логин',
            'captcha'          => 'Код подтверждения'
        );
    }
}
