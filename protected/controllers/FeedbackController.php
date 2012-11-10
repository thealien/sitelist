<?php

class FeedbackController extends Controller
{
    public function actionIndex(){
        $model = new FeedbackForm();
        $lastLinks = Links::getLastLinks(2);
        Yii::app()->params['title'] = 'Обратная связь — ' . Yii::app()->params['title'];
        $this->render('index', array(
            'lastLinks'     =>$lastLinks,
			'model' => $model
		));
    }
}