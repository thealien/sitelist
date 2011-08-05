<?php

class CommentController extends Controller
{
	/**
	 * Удаление комментария
	 * @param int $id [optional]
	 * @return 
	 */
	public function actionDelete($id = false)
	{
		$admin = Yii::app()->user->getState('admin') ? true : false;
        if(!Yii::app()->request->isPostRequest || !$id || !$admin){
            throw new CHttpException(404);  
        }
		Comments::model()->deleteByPk($id);
		$back = Yii::app()->request->urlReferrer ? Yii::app()->request->urlReferrer : '/';
        $this->redirect($back);
	}
	/**
	 * Редактирование комментария
	 * @param int $id
	 * @return 
	 */
	public function actionEdit($id){
		if(Yii::app()->user->isGuest)
            throw new CHttpException(404);
		$admin = Yii::app()->user->getState('admin') ? true : false;
        if(!$id){
            throw new CHttpException(404);    
        }
        
        $comment = Comments::model()->findByPk($id);
		if(!$comment){
			throw new CHttpException(404);
		}
		if($comment->userid!=Yii::app()->user->id && !$admin)
            throw new CHttpException(404);
        
		$errors = array();
		if(isset($_POST['Comment'])){
			$comment->attributes = $_POST['Comment'];
			if($comment->save(true, array('text'))){
				$this->redirect('/link/'.$comment->linkid);
			}
			else{
				if($comment->hasErrors())
                foreach($comment->getErrors() as $er)
                    foreach($er as $error){
                        $errors[] = $error;
                    }
			}
        }
		Yii::app()->params['title'] = 'Редактирование комментария — ' . Yii::app()->params['title'];
        $this->render('edit', array(
            'comment'   => $comment,
            'errors'    => $errors
        ));
	}
}