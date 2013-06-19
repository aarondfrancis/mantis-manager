<?php

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.

Yii::setPathOfAlias('bootstrap', dirname(__FILE__).'/../extensions/bootstrap');

$env = require(dirname(__FILE__) . '/env/' . ENVIRONMENT . '/main.php');

return array_merge_recursive($env, array(
	'basePath'=>dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name'=>'Mantis',
	'theme'=>'bounce',
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.extensions.widgets.*',
	),
	'modules'=>array(),
	
	// application components
	'components'=>array(
		'session' =>array(
			'timeout'=>3600*24*2
		),
		'user'=>array(
			'allowAutoLogin'=>true,
			'autoRenewCookie' => true,
			'authTimeout' => 31557600,
			'class'=>'WebUser'
		),
		'localtime'=>array(
	  		'class'=>'LocalTime',
	  	),
		'request'=>array(
			'class' => 'application.components.HttpRequest',
	    	'enableCsrfValidation'=>true,
	    	'dont_validate_csrf_routes'=>array(
	    		'twilio/receive',
				'twilio/callback',
				'inboundemail/quickbooks'
	    	)
	  	),
		'bootstrap'=>array(
			'class'=>'bootstrap.components.Bootstrap'
		),
		'mailer'=>array(
			'class'=>'ext.swiftMailer.SwiftMailer',
			'mailer' => 'smtp',
			'security'=>'ssl',
			'port'=>'465',
			'host'=>'smtp.mandrillapp.com',
			'From'=>'aaron@getmantis.com',
			'username'=>'aaron@getmantis.com',
			'password'=>'ZIgg5nHK9MMa6gDnjcVT8w',
    	),
		'authManager'=>array(
			'class' => 'CDbAuthManager',
			'connectionID' => 'db',
			'itemTable' => 'tbl_auth_item',
			'itemChildTable' => 'tbl_auth_item_child',
			'assignmentTable' => 'tbl_auth_assignment'
		),
		'urlManager'=>array(
			'urlFormat'=>'path',
			'caseSensitive'=>false,
			'rules'=>array(
				'<action:(index|login|logout|contact|forgot|reset)>' => 'site/<action>',
				'site/index'=>'site/index',
				'site/<view:\w+>'=>'site/page',
				'user/<id:\d+>/timecard/<type:\w+>/<setting:[\w\d-]+>'=>'user/timecard',
				'user/<id:\d+>/timecard/<type:\w+>'=>'user/timecard',
				'timecard/<id:\d+>'=>'timecard',
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<id:\d+>/<action:\w+>/*'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>/*'=>'<controller>/<action>',
			),
			'showScriptName'=>false
		),
		'errorHandler'=>array(
			'errorAction'=>'site/error',
		),
		's3' => array(
	    	'class' => 'ext.s3.ES3',
	    	'aKey'=>getenv('AWS_ACCESS_KEY'), 
	  		'sKey'=>getenv('AWS_SECRET'),
	  	),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>require(dirname(__FILE__).'/params.php')
));
