<?php



return array(
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'aaron',
	    	'generatorPaths'=>array(
	      	'bootstrap.gii',
      	),
			'ipFilters'=>array('127.0.0.1','::1'),
		),
	),
	
	'components'=>array(
		'iron'=>array(
			'class' => 'system.caching.CFileCache'
		),
	  	'cache'=>array(
	  		'class' => 'system.caching.CFileCache'
		),
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=getmantis',
			// 'connectionString' => 'mysql:host=127.0.0.1:8889;dbname=getmantis',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => 'root',
			'charset' => 'utf8',
			'schemaCachingDuration'=>180,
			'enableProfiling'=>true,
			'enableParamLogging'=>true
		),
	  	'mantisManager'=>array(
			'class'=>'MantisManager',
			'type'=>'local'
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// array(
					// 'class'=>'CProfileLogRoute',
				// ),
				array(
					'class'=>'CWebLogRoute',
				),
			),
		), 
	),
);