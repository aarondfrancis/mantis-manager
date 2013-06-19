<?php

return array(
	'components'=>array(
		'iron'=>array(
			'class' => 'system.caching.CFileCache'
		),
	  	'cache'=>array(
	  		'class' => 'system.caching.CFileCache'
		),
	  	'mantisManager'=>array(
			'class'=>'MantisManager',
			'type'=>'local'
		),
	),
);