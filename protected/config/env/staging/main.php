<?php

return array(
	'components'=>array(
	    'iron'=>array(
			'class'=>'CIronCache',
			'cacheName'=>'s3cache',
			'project_id'=>getenv('IRON_CACHE_PROJECT_ID'),
			'token'=>getenv('IRON_CACHE_TOKEN')
		),
		'mantisManager'=>array(
			'class'=>'MantisManager',
			'type'=>'remote'
		),
	    'assetManager' => array(
		    'class' => 'S3AssetManager',
		    'host' => 'getmantis.s3.amazonaws.com',
		    'bucket' => 'getmantis',
		    'path' => 'a',
	    ),
	),
);