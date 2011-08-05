<?php

class Collection extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'favs';
    }

    public function rules()
    {
        // Правила валидации
        return array(
            // Название
			array('title', 'required', 'message'=>'Вы не указали название'),
            array('title', 'match', 'pattern' => '/^[A-Za-z0-9А-Яа-я\s-\.,]+$/i','message' => 'Название содержит недопустимые символы'),
			array('title', 'length', 'max'=>255),
			// Описание
			array('desc', 'length', 'max'=>1024, 'allowEmpty' => true),
        );
    }

    public function relations()
    {
        return array(
            'links'=>array(self::MANY_MANY, 'Links',
                'links_favs(fav_id, link_id)', 'order'=> 'link_id DESC'),
			'linksCount' =>array(self::STAT, 'Links', 'links_favs(fav_id, link_id)'),
        );
    }

    public function attributeLabels()
    {
        return array(
        );
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        //$criteria->compare('id',$this->id);


        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
        ));
    }
	
	protected function beforeDelete(){
		$transaction=Yii::app()->db->beginTransaction();
        try {
            $command = Yii::app()->db->createCommand();
			// Удалить все связи сайтов и этой коллекции
            $command->delete(
                'links_favs', 
                'fav_id=:id AND user_id=:user_id', 
                array(':id'=>$this->id, ':user_id' => Yii::app()->user->id)
            );
            $transaction->commit();
        }
        catch(Exception $e){
            $transaction->rollBack();
			return false;
        }
		return true;
	}

}