<?php

$env = require(dirname(__FILE__) . '/env/' . ENVIRONMENT . '/params.php');

return array_merge_recursive($env, 
	array(
		"adminEmail"=>"help@getmantis.com",
	)
);
