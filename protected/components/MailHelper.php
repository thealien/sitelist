<?php 
class MailHelper {
	
	/**
	 * Отправка feedback-письма 
	 * @param string $mail email отправителя
	 * @param string $text текст сообщения
	 * @return bool
	 */
    static function sendFeedback($mail, $text) {
    	$ip = $_SERVER['REMOTE_ADDR'];
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
        $message = new YiiMailMessage;
        $message->view = 'feedback';
        $message->setBody(array(
            'mail' => $mail,
            'text' => $text,
			'ip' => $ip,
			'user_agent' => $user_agent
        ), 'text/html');
        $message->addTo(Yii::app()->params['adminEmail']);
        $message->from = Yii::app()->params['system_mail'];
		$message->subject = 'SiteList.in Feedback';
        return Yii::app()->mail->send($message);
    }
	/**
	 * Отправка уведомления о добавлении нового сайта
	 * @param string $title название сайта
	 * @param string $url адрес сайта
	 * @return bool
	 */
	static function sendNewSiteNotify($title, $url){
		$ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $message = new YiiMailMessage;
        $message->view = 'new_site';
        $message->setBody(array(
            'title' => $title,
            'url' => $url,
            'ip' => $ip,
            'user_agent' => $user_agent
        ), 'text/html');
        $message->addTo(Yii::app()->params['adminEmail']);
        $message->from = Yii::app()->params['system_mail'];
        $message->subject = 'SiteList.in New Site';
        return Yii::app()->mail->send($message);
	}
}

