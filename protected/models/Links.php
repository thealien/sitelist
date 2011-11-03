<?php

/**
 * This is the model class for table "links".
 *
 * The followings are the available columns in table 'links':
 * @property integer $id
 * @property string $url
 * @property integer $catid
 * @property string $title
 * @property string $desc
 * @property string $foto
 * @property integer $userid
 * @property integer $visible
 * @property string $ip
 * @property integer $rate
 * @property integer $votes
 * @property string $date
 * @property integer $pr
 * @property integer $ci
 * @property integer $pr_lastdate
 * @property integer $ci_lastdate
 */
class Links extends CActiveRecord
{
    public $voted = false; 
	public $captcha = null;
	public $isnew = false;
	public $tags = null;
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return Links the static model class
	 */
	
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'links';
	}
	
	function behaviors() {
	    return array(
	        'taggable' => array(
	            'class' => 'ext.yiiext.behaviors.model.taggable.EARTaggableBehavior',
	            'tagTable' => 'tags',
				'tagTableName' => 'name',
	            'tagModel' => 'Tag',
				'tagTablePk' => 'id',
	            'tagBindingTable' => 'links_tags',
				'tagBindingTableTagId' => 'tag_id',
				'modelTableFk' => 'link_id',
				'cacheID' => 'cache',
				'createTagsAutomatically' => true,
	        )
	    );
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// Правила валидации
		return array(
            // Общие
			array('url, catid, title, desc', 'required'),
			array('url', 'url'),
			array('catid, userid,broken', 'numerical', 'integerOnly'=>true),
			array('catid','exist','allowEmpty' => false, 'attributeName' => 'id', 'className' => 'Category'),
			array('catid, userid, visible, rate, votes, pr, ci, pr_lastdate, ci_lastdate', 'numerical', 'integerOnly'=>true),
            // Добавление
			array('captcha', 'captcha', 'allowEmpty'=>(!extension_loaded('gd') || (!Yii::app()->user->isGuest)), 'on' => 'add'),
			array('url', 'unique', 'className'=>'Links', 'attributeName'=>'url', 'message' => 'Сайт с указанным адресом уже присутствует на сайте', 'on' => 'add'),
			// Редактирование
			array('visible', 'required' , 'on' => 'edit'),
			array('visible', 'boolean' , 'on' => 'edit'),
			array('tags', 'safe', 'on' => 'edit')
		);
	}
	protected function afterFind(){
        if($this->rate > 0){
            $this->rate = '+' . $this->rate;
        }
		$this->isnew = (time()-$this->date_ts)<(259200); // 3 дня
		
		/*var_dump($this->scenario);
		if($this->scenario == 'update'){
			//$this->tags = $this->taggable->toString();
			//var_dump($this->tags);
		}*/

        return true;   
	}
	/**
	 * Перед сохранением сайта
	 * @return bool
	 */
	protected function beforeSave(){
        if(parent::beforeSave()){
		    $domain = array();
            preg_match('/(https?\:\/\/)?([a-z0-9-_.]+)/i', $this->url, $domain);
            if(isset($domain[2])){
                $this->domain = trim($domain[2],'/ ');
            }
			$this->updateTags();
		    return true;   
        }
        return false;
    }
	
	protected function beforeValidate(){
        if(parent::beforeValidate()){
            $this->url = trim($this->url, '/ ');
            return true;   
        }
        return false;
    }
	
	/**
	 * Сохранение тегов
	 * @return 
	 */
	protected function updateTags(){
		if(!is_string($this->tags)) return false;
		if(empty($this->tags)){
            $this->removeAllTags();
            return true;
        }
		$tags = explode(',',$this->tags);
		$tags = array_slice($tags, 0, 10);
		$this->setTags(implode(',', $tags));
		return true;
	}
	
    /**
     * Зачистка перед удалением сайта
     * @return bool 
     */
    protected function beforeDelete(){
		// Удалить сайт из ВСЕХ подборок
		$transaction=Yii::app()->db->beginTransaction();
        try {
            $command = Yii::app()->db->createCommand();
            $command->delete(
                'links_favs', 
                'link_id=:id', 
                array(':id'=>$this->id)
            );
            $transaction->commit();
        }
        catch(Exception $e){
            $transaction->rollBack();
            return false;
        }
		
        // Удалить комменты, если есть
        $comments = Comments::model()->deleteAll('linkid=:id', array('id'=>$this->id));
        if($this->foto!=''){
            @unlink(Yii::app()->basePath .'/../foto/' . $this->foto);
            @unlink(Yii::app()->basePath .'/../foto/' . 't_' . $this->foto);
        }
        return true;   
    }
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'url' => 'Адрес',
			'catid' => 'Категория',
			'title' => 'Название',
			'desc' => 'Описание',
			'foto' => 'Скриншот',
			'userid' => 'ID пользователя',
			'visible' => 'Видимость',
			'ip' => 'IP',
			'rate' => 'Рейтинг',
			'votes' => 'Кол-во голосов',
			'date' => 'Дата добавления',
			'pr' => 'Pr',
			'ci' => 'Ci',
			'pr_lastdate' => 'Pr Lastdate',
			'ci_lastdate' => 'Ci Lastdate',
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

		$criteria->compare('id',$this->id);

		$criteria->compare('url',$this->url,true);

		$criteria->compare('catid',$this->catid);

		$criteria->compare('userid',$this->userid);

		$criteria->compare('visible',$this->visible);

		$criteria->compare('votes',$this->votes);

		$criteria->compare('date',$this->date,true);

		$criteria->compare('pr',$this->pr);

		$criteria->compare('ci',$this->ci);

		$criteria->compare('pr_lastdate',$this->pr_lastdate);

		$criteria->compare('ci_lastdate',$this->ci_lastdate);
		
		$criteria->compare('broken',$this->broken);
		
		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
	
	public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'Users', 'userid'),
            'comments'=>array(self::HAS_MANY, 'Comments', 'linkid', 'order'=>'id DESC'),
			'comments_users'=>array(self::HAS_MANY, 'Comments', 'linkid', 'order'=>'id DESC', 'with'=>'user_profile'),
			'category'=>array(self::BELONGS_TO, 'Category', 'catid'),
			'collections' => array(
                self::MANY_MANY, 
				'Collection', 
				'links_favs(link_id,fav_id)', 
				'condition'=> 'collections.user_id=:user_id', 
				'params' => array(
				    ':user_id'=> Yii::app()->user->id
				)
			)
        );
    }
	
	/**
	 * Получение последних добавленных сайтов
	 * @param int $limit [optional] кол-во
	 * @return array of objects
	 */
	public static function getLastLinks($limit=3, $approved = true){
        $limit = intval($limit);
        if(!$limit || $limit>10){
            $limit = 3;
        }
		$query = array(
            'order'=>'id DESC',
            'limit'=>$limit
		);
		if($approved){
			$query = array_merge(
                $query,
				array(
				    'condition' =>'visible = 1'
				)
			);
		}
		return self::model()->cache(60)->findAll($query);
    }
	
	public static function getNoapprovedLinks($limit=3){
        $limit = intval($limit);
        if(!$limit){
            $limit = 3;
        }
        $query = array(
            'condition' =>'visible = 0',
            'order'=>'id DESC',
            'limit'=>$limit
        );
        return self::model()->findAll($query);
    }
	
	/**
	 * Получить сайт по ID
	 * @param int $id ID сайта
	 * @param bool $approved [optional] только утвержденные
	 * @return object or FALSE
	 */
	public static function getLink($id, $approved = true){
        $link = self::model()->findByPk(intval($id));
		if(!$link || ($approved && $link->visible!=1)){
			return false;
		}
        return $link;
    }
	
	/**
	 * Получить сайты по рейтингу
	 * @param int $offset с какого
	 * @param int $count сколько
	 * @param bool $approved [optional] только утвержденные
	 * @return array or FALSE
	 */
	public static function getLinkByRate($offset, $count, $approved = true, $only_by_date = false){
        $offset = intval($offset);
        $count = intval($count);
        if($offset < 0 || $count < 0){
            return false;
        }
		
		$query = array(
            'order'     => $only_by_date ? '`id` DESC' : '`rate` DESC, `id` DESC',
            'offset'    => $offset,
            'limit'     => $count
        );
		if($approved){
			$query = array_merge(
                $query, 
				array(
                    'condition' => 'visible=:v',
                    'params'    => array(':v'=>1))
				);
		}

		$links = self::model()->findAll($query);
        if(!$links){
            return false;
        }
        return $links;
    }
	
	public function setCollections($collections){
		$cols = Collection::model()->findAllByPk($collections,'user_id=:user_id', array(':user_id'=>Yii::app()->user->id));
		if(count($cols)!=count($collections)) return false;
		$transaction=Yii::app()->db->beginTransaction();
        try {
        	$command = Yii::app()->db->createCommand();
			$command->delete(
                'links_favs', 
				'user_id=:user_id AND link_id=:id', 
				array(':id'=>$this->id, ':user_id' => Yii::app()->user->id)
			);
			foreach($collections as $c){
                $command->insert('links_favs', array(
                    'link_id' => $this->id,
                    'fav_id' => $c,
					'user_id' => Yii::app()->user->id
                ));	
			}
            $transaction->commit();
        }
        catch(Exception $e){
            $transaction->rollBack();
			return false;
        }
		return true;
	}

}
