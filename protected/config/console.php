<?php

if(getenv('ENVIRONMENT')){
	$env = strtolower(getenv('ENVIRONMENT'));
}else{
	$env = 'local';
}	

define('ENVIRONMENT', $env);
$env = require(dirname(__FILE__) . '/env/' . ENVIRONMENT . '/console.php');

return array_merge_recursive($env, array(
	// preloading 'log' component
	'preload'=>array('log'),

	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.extensions.widgets.*',
	),
	'components'=>array(
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
