<?php

/**
 * This is the model class for table "category".
 *
 * The followings are the available columns in table 'category':
 * @property integer $id
 * @property string $catname
 * @property string $icon
 */
class Category extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'category';
	}
	
	public function getList(){
		$result = array();
		foreach(self::model()->findAll(array('order'=>'catname ASC')) as $c){
			$result[$c->id] = $c->catname;
		}
		return $result;
	}

	public function rules()
	{
		// Правила валидации
		return array(
            // Общие
			array(' catname, alias', 'required'),
            array('catname', 'match', 'pattern' => '/^[a-zA-Zа-яА-Яы0-9\s-.,]+/i','message' => 'Название категории содержит недопустимые символы'),
		);
	}

	public function relations()
    {
        return array(
            'linksCountAll'     =>array(self::STAT, 'Links', 'catid'),
			'linksCount'     =>array(self::STAT, 'Links', 'catid', 'condition' => 'visible=1'),
            'newLinksCount'  =>array(self::STAT, 'Links', 'catid', 'condition' => 'visible=1 AND (DATE_SUB(NOW(), INTERVAL 3 DAY)<date)'),
			'links'          =>array(self::HAS_MANY, 'Links', 'catid', 'condition' => 'visible=1', 'order' => 'links.id DESC'),
        );
    }

	public function attributeLabels()
	{
		return array(
			'id'         => 'ID категории',
			'catname'    => 'Название категории',
			'alias'      => 'Алиас'
		);
	}

	public static function getRootCats($full = true){
		$cats = self::model()->cache(60, null, 3)->findAll(array(
			'order'      =>'t.catname ASC',
			'with' => $full ? array('linksCount', 'newLinksCount') : array()
        ));
		return $cats;
	}

}