<?php

if(getenv('ENVIRONMENT')){
	$env = strtolower(getenv('ENVIRONMENT'));
}else{
	$env = 'local';
}	

define('ENVIRONMENT', $env);
$env = require(dirname(__FILE__) . '/env/' . ENVIRONMENT . '/console.php');

return array_merge_recursive($env, array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'../',
	'name'=>'My Console Application',

	// preloading 'log' component
	'preload'=>array('log'),

	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.extensions.widgets.*',
	),
	'components'=>array(
		'localtime'=>array(
	  		'class'=>'LocalTime',
	  	),
		'authManager'=>array(
			'class' => 'CDbAuthManager',
			'connectionID' => 'db',
			'itemTable' => 'tbl_auth_item',
			'itemChildTable' => 'tbl_auth_item_child',
			'assignmentTable' => 'tbl_auth_assignment'
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
	)
));
