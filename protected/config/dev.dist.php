<?php

// Config for Develop env
return CMap::mergeArray(
    require(dirname(__FILE__).'/main.php'),
    array(
    
        'preload'=>array('log'),
        
        'components'=>array(
            // CutyCapt скринер
            'cutycapt'=>array(
                'class' => 'application.components.CutyCapt',
                'path' => '/home/*******/CutyCapt',
                'user_xvfb' => true,
                'filetype' => 'jpg'
            ),
            
            // Настройки БД
            'db'=>array(
                'connectionString' => 'mysql:host=localhost;dbname=*****',
                'emulatePrepare' => false,
                'username' => '*****',
                'password' => '*****',
                'charset' => 'utf8',
                'enableProfiling'=>true,
                'enableParamLogging' => true,
            ),
            
            // Логирование
            'log'=>array(
                'class'=>'CLogRouter',
                'routes'=>array(
                    array(
                        'class'=>'CProfileLogRoute',
                        'levels'=>'profile',
                        'enabled'=>true,
                    ),
                    array(
                        'class' => 'CWebLogRoute',
                        'categories' => 'application',
                        'levels'=>'error, warning, trace, profile, info',
                    ),
                ),
            ),
        ),
        
        'modules'=>array(
            'gii'=>array(
                'class'=>'system.gii.GiiModule',
                'password'=>'*****',
                 'ipFilters'=>array('*.*.*.*'),
                 'newFileMode'=>0666,
                 'newDirMode'=>0777
            ),
        ),
        
        'params'=>array(
            'adminEmail'=>'***@**.**',
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
    )
);