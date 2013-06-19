<?php
class Controller extends CController
{
	public $layout='//layouts/column1';
	public $menu=array();

	public function asset($asset, $echo=true){
		$url = Yii::app()->mantisManager->getAsset($asset);
		if(!$url) Yii::log("Asset Missing: $asset", 'warning');
		if($echo) echo $url;
		if(!$echo) return $url;
	}
}