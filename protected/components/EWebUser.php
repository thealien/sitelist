<?php

class EWebUser extends CWebUser {
	
	protected $role;
    
	public function getRole(){
		if($this->isGuest)
            return false;
		if(!is_null($this->role))
            return $this->role;
		$role_key = 'user_'.Yii::app()->user->id.'.role';
		if(Yii::app()->cache){
			$role = Yii::app()->cache->get($role_key);
			if($role!==false){
				return $this->role = $role;
			}
		}
		$user = Users::model()->findByPk($this->id);
		if(!$user)
            return false;
        $this->role = $user->role;
		if(Yii::app()->cache)
            // save role for 30 min
			$role = Yii::app()->cache->set($role_key, $this->role, 60*30);
		return $this->role;
	}	
	
	public function hasRole($role){
		return $this->getRole() === $role;
	}
	
	public function isAdmin(){
		return $this->hasRole('admin');
	}
	
	public function isModer(){
		return ($this->hasRole('admin') || $this->hasRole('moder'));
	}
}