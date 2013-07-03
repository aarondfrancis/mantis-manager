<?php

class MantisCommand extends CConsoleCommand {
	public function actionIndex($type='local'){
		if($type !== 'local' && $type !== 'remote'){
			echo "Option --type must be either local or remote. \r\n";
			Yii::app()->end();
		}
		echo "Publishing Assets \r\n";
		$mantisManager = Yii::app()->mantisManager;
		$mantisManager->setType($type);
		$mantisManager->publish();
	}

	public function actionReset($type='local'){
		if($type !== 'local' && $type !== 'remote'){
			echo "Option --type must be either local or remote. \r\n";
			Yii::app()->end();
		}
		echo "Resetting Assets \r\n";
		$mantisManager = Yii::app()->mantisManager;
		$mantisManager->setType($type);
		$mantisManager->reset();
	}

	public function actionWatch(){
		$mantisManager = Yii::app()->mantisManager;
		$mantisManager->watch();
	}
}
