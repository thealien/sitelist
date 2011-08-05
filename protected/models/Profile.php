<?php

class Profile extends CActiveRecord
{
	public $_avatar = null;
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'profile';
    }

    public function rules()
    {
        return array(
            // user_id
			array('user_id','exist','allowEmpty' => false, 'attributeName' => 'userID', 'className' => 'Users', 'message' => '{attribute} не существует'),
			// icq
            array('icq', 'match', 'allowEmpty' => true, 'pattern' => '/^[0-9-]+$/i','message' => 'Номер ICQ имеет неверный формат'),
			// from
			array('from', 'length', 'max'=>150, 'allowEmpty' => true),
			// birthday
			array('birthday', 'date', 'allowEmpty' => true, 'format' => 'dd.MM.yyyy','message' => 'День рождения указан ошибочно'),
			// site
			array('site', 'url', 'allowEmpty' => true, 'message' => 'Неверный формат адреса сайта'),
			// skype
			array('skype', 'match', 'allowEmpty' => true, 'pattern' => '/^[a-zA-Z]+[a-zA-Z0-9-\.]+$/i','message' => 'Skype имеет неверный формат'),
			// avatar
			array('_avatar', 'file', 'allowEmpty' => true, 'types'=>'jpg, gif, png', 'wrongType'=> 'Запрещенный тип файла', 'maxSize' => 1024*1024),
			
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
          
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(

        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        //$criteria->compare('id',$this->id);


        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
        ));
    }
	
	public function afterFind(){
		return true;
	} 
}