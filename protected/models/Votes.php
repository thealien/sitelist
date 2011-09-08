<?php

/**
 * This is the model class for table "votes".
 *
 * The followings are the available columns in table 'votes':
 * @property integer $link_id
 * @property string $ip
 */
class Votes extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'votes';
	}

	public function rules()
	{
		return array(
			array('link_id, ip, vote', 'required'),
			array('link_id', 'numerical', 'integerOnly'=>true),
			array('ip', 'length', 'max'=>15),
			array('vote', 'in', 'allowEmpty' => false, 'range' => array('1', '-1', 1, -1)),
			array('link_id, ip', 'safe', 'on'=>'search'),
			
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'link_id' => 'Link',
			'ip' => 'Ip',
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

		$criteria->compare('link_id',$this->link_id);

		$criteria->compare('ip',$this->ip,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
    
    public static function canVote($link_id){
        $link_id = intval($link_id);
        if($link_id<1) return false;
		
		if(!Yii::app()->user->isGuest){
			$uid = Yii::app()->user->id;
			$vote = Votes::model()->findByAttributes(
                array('link_id'=>$link_id, 'user_id'=>$uid)
            );
            if($vote!==null) return false;
		}

        if(isset($_COOKIE['votes'])){
            static $v;
            if(is_null($v)){
                $v = explode(',', (string)$_COOKIE['votes']);    
            }
            if(in_array($link_id, $v)){
                return false;
            }
            else{
                $ip = $_SERVER['REMOTE_ADDR'];
                $vote = Votes::model()->findByAttributes(
                    array('link_id'=>$link_id, 'ip'=>$ip), 
                    array(
                        'condition'=>'(DATE_SUB(NOW(), INTERVAL 12 HOUR) < `date`)',
                        'order'=>'date DESC'
                    )
                );
                if($vote!==null) return false;
            }
        }
        else{
            $ip = $_SERVER['REMOTE_ADDR'];
            $vote = Votes::model()->findByAttributes(
            array('link_id'=>$link_id, 'ip'=>$ip), 
                array(
                    //'condition'=>'(NOW()-date)<3600',
					'condition'=>'(DATE_SUB(NOW(), INTERVAL 12 HOUR) < `date`)',
                    'order'=>'date DESC'
                )
            );
            if($vote!==null) return false;
        }
        return true;
    }
    
    public static function addVote($link_id, $vote){
        $link_id = intval($link_id);
        $link = Links::model()->findByPk($link_id);
        if(!$link){
            return false;
        }
        $vote = intval($vote);
        if(!in_array($vote, array('1', '-1'))){
            exit();
        }
		
		if(self::isBanned($_SERVER['REMOTE_ADDR'], $link_id)) return false; // banned
		
        $v = new Votes('add');
        $v->link_id = $link->id;
        $v->ip = $_SERVER['REMOTE_ADDR'];
		$v->vote = $vote;
		if(!Yii::app()->user->isGuest){
			$v->user_id = Yii::app()->user->id;
		}
        if(!$v->save()){
            return false;
        }
        
        $link->rate+=$vote;
        $link->votes++;
        if(!$link->save(false, array('rate', 'votes'))){
            return false;
        }
        $votes = array();
        $votes[] = $link->id;
        if(isset($_COOKIE['votes'])){
            $v = explode(',', (string)$_COOKIE['votes']);
            foreach($v as $v_){
                $votes[] = intval($v_);
            }
            $votes = array_unique($votes);
        }
        setcookie('votes', implode(',', $votes) , time()+60*60*24*365, '/', $_SERVER['SERVER_NAME']);
        return array('rate'=>$link->rate, 'votes'=>$link->votes);
    }
	
	public static function isBanned($ip, $link_id){
		$bans = array(
            '81.24.208.96' => array(5708)
		);
		if(!isset($bans[$ip])) return false;
		if(!is_array($bans[$ip])) return false;
		if(!in_array($link_id, $bans[$ip])) return false;
		return true;
	}
}