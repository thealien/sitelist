<?php

class FeedbackController extends Controller
{
    public function actionIndex(){
        $model = new FeedbackForm();
		if(isset($_POST['FeedbackForm'])){
            $model->attributes = $_POST['FeedbackForm'];
            if($model->validate()){
                if(MailHelper::sendFeedback($model->email, $model->text)){
                	Yii::app()->user->setFlash('feedback',true);
					$this->refresh();
                }
                else{
                	$model->addError('text', 'Ошибка отправки, попробуйте позже.');
                }
            }   
        }

        $lastLinks = Links::getLastLinks(2);
        Yii::app()->params['title'] = 'Обратная связь — ' . Yii::app()->params['title'];
        $this->render('index', array(
            'lastLinks'     =>$lastLinks,
			'model' => $model
		));
    }
}