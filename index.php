<?php


if(strpos($_SERVER['SERVER_NAME'], 'dev.')===0){
	// Dev
	defined('YII_DEBUG') or define('YII_DEBUG',true);
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);
	$config=dirname(__FILE__).'/protected/config/dev.php';
}
else{
	// Prod
    $config=dirname(__FILE__).'/protected/config/prod.php';	
}

$yii=dirname(__FILE__).'/../yii.framework/yii.php';
require_once($yii);
Yii::createWebApplication($config)->run();
