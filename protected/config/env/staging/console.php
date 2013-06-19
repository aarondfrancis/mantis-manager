<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	// application components
	'components'=>array(
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
		'cache'=>array(
		    'class'=>'CMemCacheSASL',
		    'server'=> array(
				'host'=>getenv('MEMCACHE_SERVERS'),
				'username' => getenv('MEMCACHE_USERNAME'),
				'password' => getenv('MEMCACHE_PASSWORD'),
				'port'=>11211,
			),
	    ),
	    'mandrill'=>array(
			'class'=>'Mandrill'
		),
	    'iron'=>array(
	    	'class'=>'CIronCache',
	    	'cacheName'=>'s3cache',
	    	'project_id'=>getenv('IRON_CACHE_PROJECT_ID'),
			'token'=>getenv('IRON_CACHE_TOKEN')
		),
	    'assetManager' => array(
		    'class' => 'S3AssetManager',
		    // 'host' => 'd2y6ktxczt3t5w.cloudfront.net', // changing this you can point to your CloudFront hostname
		    'host' => 'getmantis.s3.amazonaws.com',
		    'bucket' => 'getmantis',
		    'path' => 'assets', //or any other folder you want
	    ),
	),
);