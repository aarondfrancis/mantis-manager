<?php
require("awskeys.php");

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(

	// application components
	'components'=>array(
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=getmantis',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => 'root',
			'charset' => 'utf8',
		),
		'localAssets'=>array(
			'class' => 'CAssetManager',
			'basePath' => Yii::getPathOfAlias('application') . '../assets',
            'baseUrl' => '/assets'
		),
	    'remoteAssets' => array(
		    'class' => 'S3AssetManager',
		    'host' => 'getmantis.s3.amazonaws.com',
		    'bucket' => 'getmantis',
		    'path' => 'a',
		    'cacheComponent' => 'fileCache'
	    ),
	    'fileCache'=>array(
	    	'class'=>'system.caching.CFileCache'
    	),
		's3' => array(
	    	'class' => 'ext.s3.ES3',
	    	'aKey'=>$akey, 
	  		'sKey'=>$skey,
	  	),
	    'mantisManager'=>array(
			'class'=>'MantisManager',
			'runtimePath'=>'application.runtime',
			'assetsPath'=>'application.assets',
			'localManager'=>'localAssets',
			'remoteManager'=>'remoteAssets',
			'ignore'=>array(
				'*.psd'
			),
			'css'=>array(
				'combine'=>array(
					'css/combined.css' => array(
						'css/base.css',
						'css/blue.css',
						'css/calendar.css',
						'js/bootstrap-select/bootstrap-select.css',
						'js/bootstrap-datepicker/datepicker.css',
						'css/styles.css',
					)
				),
				'minify'=>true
			),
			'js'=>array(
				'combine'=>array(
					'js/combined.js' => array(
						'js/bootstrap.js',
						'js/bootstrap-select/bootstrap-select.js',
						'js/bootstrap-datepicker/bootstrap-datepicker.js',
						'js/mantis.js'
					)
				),
				'minify'=>true
			)
		),
	),
);