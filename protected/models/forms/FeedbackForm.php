<?php

class FeedbackForm extends CFormModel {
	public $email;
	public $text;
	
	public function rules()
    {
        return array(
            array('email, text', 'required'),
            array('email', 'email'),
        );
    }
	
	public function attributeLabels()
    {
        return array(
            'email'         => 'Email',
            'text'          => 'Сообщение'
        );
    }
}
