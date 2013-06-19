<?php

$env = require(dirname(__FILE__) . '/env/' . ENVIRONMENT . '/main.php');

return array_merge_recursive($env, array(
	// application components
	'components'=>array(
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
