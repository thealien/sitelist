<?php

class UserIdentity extends CUserIdentity
{
	private $_id;
	
    public function authenticate()
    {
        $record = Users::model()->findByAttributes(array('username'=>$this->username));
        if($record===null)
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        else if($record->password!==md5($this->password))
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        else
        {
            $this->_id = $record->id;
            $this->errorCode=self::ERROR_NONE;
        }
        return !$this->errorCode;
    }
	
	public function authenticateById($id)
    {
        $record = Users::model()->findByPk($id);
        if($record===null)
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        else
        {
			$this->username = $record->username;
            $this->_id = $record->id;
            $this->errorCode=self::ERROR_NONE;
        }
        return !$this->errorCode;
    }
 
    public function getId()
    {
        return $this->_id;
    }
}