<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>'sitelist',
	'language' => 'ru',
    'charset'=>'utf-8',
    'defaultController'=>'main',

    // autoloading model and component classes
    'import'=>array(
        'application.models.*',
        'application.components.*',
        'application.extensions.yii-mail.*',
    ),

    // application components
    'components'=>array(
        'user'=>array(
            'allowAutoLogin' => true,
            'identityCookie' => array(
                'domain' => $_SERVER['SERVER_NAME']
            ),
        ),
        'urlManager'=>array(
            'urlFormat'=>'path',
            'showScriptName'=>false,
            'rules'=>array(
                'link/<id:\d+>/*'=>'link/view',
                'category/<id:\d+>/<alias:([a-z]*)>/<page:\d*>/'=>'category/view',
                'category/<id:\d+>/<alias:([a-z]*)>/'=>'category/view',
                'category/<id:\d+>/'=>'category/view',
                'add'=>'link/add',
                'about'=>'main/about',
                'top/<page:\d+>/*'=>'top/index',
                'new/<page:\d+>/*'=>'new/index',
                'rss/?<category:\w*>/?'=>'main/rss',
                'login'=>'user/login',
                'logout'=>'user/logout',
                'register'=>'user/register',
				'users/<page:\d+>'=>'main/users',
				'users'=>'main/users',
                'profile/email/?<hash:\w*>'=>'profile/email',
                'profile/pass'=>'profile/pass',
                'user/<user:\w+>/fav/?'=>'user/fav',
				'user/captcha' => 'user/captcha',
                'user/<user:[\w\s]+>'=>'user/index',
				'collection/<id:\d+>'=>'collection/index',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>/<id:.+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ),
        ),
        
        // CutyCapt скринер ( http://cutycapt.sourceforge.net )
        'cutycapt'=>array(
            'class' => 'application.components.CutyCapt',
            'path' => '/PATH/TO/CutyCapt',
            'user_xvfb' => true,
            'filetype' => 'jpg'
        ),
        'cache'=>array(
            'class'=>'system.caching.CMemCache',
            'servers'=>array(
                array('host'=>'127.0.0.1', 'port'=>11211)
            ),
        ),
        // Настройки БД
        'db'=>array(
            'connectionString' => 'mysql:host=localhost;dbname=dbname',
            'emulatePrepare' => false,
            'username' => '********************',
            'password' => '********************',
            'charset' => 'utf8',
            'enableProfiling'=>true,
            'enableParamLogging' => true,
        ),
        
        // Контроллер, если возникла ошибка
        'errorHandler'=>array(
            'errorAction'=>'main/error',
        ),
        
        
        
        // Twig шаблонизатор
        'viewRenderer'=>array(
            'class'             =>'ext.yiiext.renderers.twig.ETwigViewRenderer',
            'options'           => array(
                'charset'           => 'utf-8',
                'trim_blocks'       => FALSE,
                'strict_variables'  => false,
                'auto_reload'       => true,
                'autoescape'        => false,
                'minify'            => true
            ),
            'extentions'        => array(
                'My_Twig_Extension' // file vendors/My_Twig_Extension.php must exists
            ),
        ),
		
		// Mailer ( http://www.yiiframework.com/extension/mail )
        'mail' => array(
            'class' => 'ext.yii-mail.YiiMail',
            'transportType' => 'php',
            'viewPath' => 'application.views.layouts.mail',
            'logging' => true,
            'dryRun' => false
        ),
    ),
    
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params'=>array(
        'adminEmail' => 'your@mail.com',
		'system_mail' => 'mail@' . $_SERVER['SERVER_NAME'], 
        'title' => 'Каталог сайтов SiteList',
        'SITE_RATE_DATE_EXP' => 3*24*60*60,
        'IMAGES_DIR' => realpath(dirname(__FILE__) . '/../..') . '/foto/',
		'AVATARS_DIR' => realpath(dirname(__FILE__) . '/../..') . '/images/avatars/',
        'sphinx'=> array(
            'host' => '127.0.0.1',
            'port' => 3312
        ),
        'link_pager' => array(
            'nextPageLabel'=>'&rarr;',
            'prevPageLabel'=>'&larr;',
            'firstPageLabel'=> 'начало',
            'lastPageLabel' => 'в конец',
            'header' => '',
            'cssFile' =>false
        )
        
    ),
);