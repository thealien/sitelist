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
		'application.models.forms.*',
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
		'session' => array(
            //'class' => 'CDbHttpSession',
            'cookieParams' => array('domain' => $_SERVER['SERVER_NAME']),
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
				'top'=>'top/index',
				
				'feedback'=>'feedback/index',
				
                'new/<page:\d+>/*'=>'new/index',
				'new'=>'new/index',
				
                'rss/<category:\w*>/'=>'main/rss',
				'rss'=>'main/rss',
				
                'login'=>'user/login',
                'logout'=>'user/logout',
                'register'=>'user/register',
				
				'users/<page:\d+>'=>'main/users',
				'users'=>'main/users',
				
                'profile/email/<hash:\w*>'=>'profile/email',
				'profile/email'=>'profile/email',
                'profile/pass'=>'profile/pass',
				'profile'=>'profile/index',
				
                'user/<user:\w+>/fav/?'=>'user/fav',
				'user/captcha' => 'user/captcha',
				'user/openid' => 'user/openid',
				'user/oauth/facebook' => 'user/oauthfacebook',
				'user/oauth/vkontakte' => 'user/oauthvkontakte',
				'user/oauth/twitter' => 'user/oauthtwitter',
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
            'filetype' => 'jpg',
			'user_agent' => 'Mozilla/5.0 (Windows; I; Windows NT 5.1; ru; rv:1.9.2.13) Gecko/20100101 Firefox/4.0'
			
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
			'register_static' => array(
                'CHtml',
			)
        ),
		
		// Mailer ( http://www.yiiframework.com/extension/mail )
        'mail' => array(
            'class' => 'ext.yii-mail.YiiMail',
            'transportType' => 'php',
            'viewPath' => 'application.views.layouts.mail',
            'logging' => true,
            'dryRun' => false
        ),
		
		'loid' => array(
            'class' => 'application.extensions.lightopenid.loid',
        ),
		
		'widgetFactory' => array(
            'widgets' => array(
                'CLinkPager' => array(
				    'pageSize' => 15,
                    'nextPageLabel'=>'&rarr;',
                    'prevPageLabel'=>'&larr;',
                    'firstPageLabel'=> 'начало',
                    'lastPageLabel' => 'в конец',
                    'header' => '',
                    'cssFile' =>false
				),
				'CCaptcha' => array(
                    'showRefreshButton'=>false,
                    'clickableImage'=>true,
                    'imageOptions'=>array(
                        'class' => 'captha',
		                'alt'=>'проверочный код',
		                'title'=>'Кликни по картинке, чтобы сменить код',
		                'border'=>1,
		                'width'=>'100px',
		                'height'=>'40px'
		            )
				)
			)
		),
		'clientScript' => array(
            'scriptMap' => array(
                // TODO
                'jquery.js' => false,
			    'jquery.min,js' => false,
			)
		),
    ),
    
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
		'facebook' => array(
            'app_id' => null,
			'secret' => null
		),
        'vkontakte' => array(
            'app_id' => null,
            'secret' => null
        ),
		'twitter' => array(
            'consumer_key' => null,
			'consumer_secret' => null
		)
        
    ),
);