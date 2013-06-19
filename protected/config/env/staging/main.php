<?php

return array(
	'components'=>array(
		'cache'=>array(
		    'class'=>'CMemCacheSASL',
		    'server'=> array(
				'host'=>getenv('MEMCACHE_SERVERS'),
				'username' => getenv('MEMCACHE_USERNAME'),
				'password' => getenv('MEMCACHE_PASSWORD'),
				'port'=>11211,
			),
	    ),
	    'session'=>array(
			'class'=>'CCacheHttpSession'
		),
	    'iron'=>array(
			'class'=>'CIronCache',
			'cacheName'=>'s3cache',
			'project_id'=>getenv('IRON_CACHE_PROJECT_ID'),
			'token'=>getenv('IRON_CACHE_TOKEN')
		),
		'db'=>array(
			'connectionString' => 'mysql:host=' .getenv('AMAZON_RDS_HOST') . ';dbname=' . getenv('AMAZON_RDS_DBNAME') . ';',
			'username' => getenv('AMAZON_RDS_USERNAME'),
			'password' => getenv('AMAZON_RDS_PASSWORD'),
			'emulatePrepare' => true,
			'charset' => 'utf8',
			'schemaCachingDuration'=>180,
			'enableProfiling'=>true,
			'enableParamLogging'=>true
		),
		'mantisManager'=>array(
			'class'=>'MantisManager',
			'type'=>'remote'
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				array(
					'class'=>'CHerokuLogRoute',
					'levels'=>'error, warning, info'
				),
				// array(
					// 'class'=>'CProfileLogRoute',
				// ),
				array(
					'class'=>'CWebLogRoute',
				),
			),
		),
	    'assetManager' => array(
		    'class' => 'S3AssetManager',
		    // 'host' => 'd2y6ktxczt3t5w.cloudfront.net', // changing this you can point to your CloudFront hostname
		    'host' => 'getmantis.s3.amazonaws.com',
		    'bucket' => 'getmantis',
		    'path' => 'a',
	    ),
	),
);