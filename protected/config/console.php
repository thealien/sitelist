<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
/*
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Console Application',
);
*/

// Config for Console app
return CMap::mergeArray(
    require(dirname(__FILE__).'/prod.php')
);