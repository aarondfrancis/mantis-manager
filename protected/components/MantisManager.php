<?php

class MantisManager extends CApplicationComponent {

	public $assetsPath = 'application.assets';
	public $runtimePath = 'application.runtime';
	public $ignore = array();
	public $css = array();
	public $js = array();
	public $startVersion = 0;
	public $type = 'local';

	public $localManager;
	public $remoteManager;


	private $_processQueue = array();

	private $_assetsPath;
	private $_cachePath;
	private $_cacheFolder;
	private $_cache;
	private $_run;
	private $_watch = false;

	public function init(){
		$this->consoleEcho("Mantis Manager engaged \r\n", "0;35");
		$this->_run = time();
		$this->_assetsPath = Yii::getPathOfAlias($this->assetsPath);


		$this->ignore = $this->extend(array(
			// ignore hidden files
			'.*',
			'*/.*'
		), $this->ignore);

		$this->css = $this->extend(array(
			'combine' => array(),
			'minify' => true
		), $this->css);

		$this->js = $this->extend(array(
			'combine' => array(),
			'minify' => true
		), $this->js);


		// convert the ignore patterns to regular expressions
		foreach($this->ignore as &$ignore){
			$ignore = $this->convertPattern($ignore);
		}

		$this->setType($this->type);
	}

	public function consoleEcho($msg, $color=null, $showOnWatch=false){
		if(Yii::app() instanceof CConsoleApplication){
			if(!$this->_watch || ($this->_watch && $showOnWatch)){
				if(!is_null($color)){
					echo "\033[{$color}m" . $msg . "\033[0m";
				}else{
					echo $msg;
				}
			}
		}
	}

	public function getAsset($asset){
		$cache = $this->getCache();
		return array_key_exists($asset, $cache) ? $cache[$asset]['pub'] : false;
	}

	public function setType($type){
		$this->type=$type;
		$this->_cachePath = Yii::getPathOfAlias($this->runtimePath) . "/mantisManager-{$this->type}.cache";
		$this->_cacheFolder = Yii::getPathOfAlias($this->runtimePath) . "/mantisManager/{$this->type}";
	}

	private function convertPattern($pattern){
		$pattern = preg_replace('/([^*])/e', 'preg_quote("$1", "/")', $pattern);
		$pattern = str_replace('*', '.*', $pattern);
		return $pattern;
	}

	public function getCache(){
		// get the array of published assets
		if($this->_cache) return $this->_cache;
		return $this->_cache = file_exists($this->_cachePath) ? unserialize(file_get_contents($this->_cachePath)) : array();
	}

	private function setCache($cache = array()){
		$this->_cache = $cache;
		file_put_contents($this->_cachePath, serialize($cache));
	}

	public function reset(){
		if(is_dir($this->_cacheFolder))
			$this->deleteDirectory($this->_cacheFolder);

		if(is_file($this->_cachePath))
			unlink($this->_cachePath);
	}

	public function publish(){
		$path = Yii::getPathOfAlias($this->assetsPath);
		$currentFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);

		// loop through every file in the assets folder and check to see if 
		// any of them need to be published
	    foreach($currentFiles as $file) {
	    	if($this->testIgnored($file)){
	    		$this->consoleEcho("Ignored ", "0;31");
	    		$this->consoleEcho($file->getFileName() . "\r\n");
		    	continue;	
	    	} 
			$ext = pathinfo($file->getPathName(), PATHINFO_EXTENSION);

			if($ext == "css" || $ext == "js"){
				$this->_processQueue[$file->getPathName()] = array();
				continue;
			}

	    	$this->publishFile($file);
	    }

	    $this->processProcessQueue();
	    $this->combineFiles();

	    // print_r($this->_cache);
	}

	private function publishFile($file, $src=null){
		if(is_null($src)) $src = $file;
		$cache = $this->getCache();

		$relative = str_replace($this->_assetsPath, "", $file->getPathName());

		if(!array_key_exists($relative, $cache)){
			$fileCache = array(
				'sha'=>'getmantis.com', 
				'ver'=>$this->startVersion,
				'run'=>0
			);	
		}else{
			$fileCache = $cache[$relative];
		}

		// check to see if the file has changed
		if($fileCache['sha'] !== sha1_file($src)){
			$this->consoleEcho("Updating ", "0;32", true);
			$this->consoleEcho($file->getFileName() . " \r\n", null, true);

			$fileCache['ver'] = $fileCache['ver']+1;
			$fileCache['sha'] = sha1_file($src);
			if($this->type=="local"){
				$publicAssetPath = Yii::getPathOfAlias('application') . "/../assets/" . $fileCache['ver'];
				if(!is_dir($publicAssetPath)) mkdir($publicAssetPath);
				Yii::app()->{$this->localManager}->setBasePath($publicAssetPath);
				Yii::app()->{$this->localManager}->setBaseUrl('/assets/'.$fileCache['ver']);

				$fileCache['pub'] = Yii::app()->{$this->localManager}->publish($src->getPathName(), false, -1, true);
			}
			if($this->type=="remote"){
				$fileCache['pub'] = Yii::app()->{$this->remoteManager}->publish($src->getPathName(), false, -1, true, $fileCache['ver']);
			}
		}else{
			$this->consoleEcho("No Change ", "0;33");
			$this->consoleEcho($file->getFileName() . "\r\n");
		}

		// we use this later for our dependency checks
		$fileCache['run'] = $this->_run;
		$cache[$relative] = $fileCache;

		$this->setCache($cache);
	}

	private function processProcessQueue(){
		$this->consoleEcho("Processing CSS & JS files \r\n", "0;35");

		foreach($this->_processQueue as $filename => &$dependencies){
			$this->consoleEcho("Checking for referenced assets in $filename \r\n");

			$contents = file_get_contents($filename);
			$pattern = "/{{asset\\(\"(.+)\"\\)}}/";
			preg_match_all($pattern, $contents, $matches);

			if(count($matches) > 1){
				// check each dependency
				foreach($matches[1] as &$match){
					$asEntered = $match;
					if(substr($match,0,1) === "/"){
						$match = $this->_assetsPath . $match;
					}else{
						$match = pathinfo($filename, PATHINFO_DIRNAME) . "/$match";
					}

					if(!realpath($match)){
						$this->consoleEcho("\r\nWarning! While processing $filename, a referenced asset was not found: $match.  \r\n \r\nAborting....\r\n \r\n","0;31", true);						
						Yii::app()->end();
					}
					$match = array(
						'asEntered' => $asEntered,
						'real' => realpath($match)
					);

				}
			}

			$dependencies = isset($matches[1]) ? $matches[1] : array();
		}

		// sort by the lowest number of dependencies
		uasort($this->_processQueue, function($a, $b) {return count($a) - count($b);});

		// process each file
		foreach($this->_processQueue as $filename => $dependencies){
			$this->processFile($filename);
		}
	}

	private function processFile($filename, $i=0){
		$dependencies = array_key_exists($filename, $this->_processQueue) ? $this->_processQueue[$filename] : array();

		$contents = file_get_contents($filename);

		$patterns = array();
		$replaces = array();

		foreach($dependencies as $dependency){
			$cache = $this->getCache();

			// if the dependency hasn't been published yet (not in the cache)
			// or if it hasn't been updated on this run, process it
			if(!array_key_exists($dependency['real'], $cache) || $cache[$dependency['real']]['run'] !== $this->_run){
				if($i == 200){
					$this->consoleEcho("\r\n \r\nError: Entered into an infinite asset reference loop. \r\n\r\n", null, true);
					Yii::app()->end();
				}
				$i++;
				$this->processFile($dependency['real'], $i);

			// otherwise replace with the published filename
			}else{
				$search = "{{asset(\"" . $dependency['asEntered'] . "\")}}";
				$contents = str_replace($search, $this->cache[$dependency['real']]['pub'], $contents);
			}
		}

		$file = new SplFileInfo($filename);
		$src = new SplFileInfo(str_replace($this->_assetsPath, $this->_cacheFolder, $filename));

		$dir = $src->getPath();

		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);			
		}

		if($this->needsMinifying($src)) $contents = $this->minify($src, $contents);


		file_put_contents($src, $contents);

		$this->publishFile($file, $src);
	}

	private function needsMinifying($src){
		$ext = pathinfo($src->getPathName(), PATHINFO_EXTENSION);

		if($ext != "css" && $ext != "js") return false;

		// do not minify files with .min. in the name. 
		// eg: bootstrap.min.js
		if(strpos($src->getFileName(), ".min.") !== false){
			$this->consoleEcho("Minify Skipped ", "0;31");
			$this->consoleEcho($src->getFileName() . "\r\n");
			return false;
		}

		if($ext == "css" && !$this->css['minify']) return false;
		if($ext == "js" && !$this->js['minify']) return false;

		return true;

			
	}

	private function minify($src, $contents){
		$ext = pathinfo($src->getPathName(), PATHINFO_EXTENSION);
		if($ext == "css"){
			$this->consoleEcho("Minifying ", "0;32");
			$this->consoleEcho($src->getFileName() . "\r\n");			
			$contents = $this->minifyCSS($contents);
		}

		if($ext == "js"){
			$this->consoleEcho("Minifying ", "0;32");
			$this->consoleEcho($src->getFileName() . "\r\n");			
			$contents = $this->minifyJS($contents);		
		}

		return $contents;
	}	

	private function testIgnored($file){
		if(is_dir($file)) return true;
		foreach($this->ignore as $ignore){
			if ((bool) preg_match('/^' . $ignore . '$/i', $file)){
				return true;	
			} 
		}
		return false;
	}

	private function combineFiles(){
		$combine = array_merge($this->css['combine'], $this->js['combine']);
		foreach($combine as $filename=>$files){
			$this->consoleEcho("Combining ", "0;32");
			$this->consoleEcho("for " . $this->_assetsPath . '/' . $filename . "\r\n");	
			$content = $this->combine($files);
			
			$ext = pathinfo($filename, PATHINFO_EXTENSION);

			if($ext == "css" && $this->css['minify']){
				$this->consoleEcho("Minifying ", "0;32");
				$this->consoleEcho(" $filename \r\n");
				$content = $this->minifyCSS($content);
			}
			if($ext == "js" && $this->js['minify']){
				$this->consoleEcho("Minifying ", "0;32");
				$this->consoleEcho(" $filename \r\n");
				$content = $this->minifyJS($content);
			}

			file_put_contents($this->_cacheFolder . '/' . $filename, $content);

			$file = new SplFileInfo($this->_assetsPath . '/' . $filename);
			$src = new SplFileInfo($this->_cacheFolder . '/' . $filename);

			$this->publishFile($file, $src);
		}
	}

	private function combine($files){
		$content = "";
		foreach($files as $file){
			$this->consoleEcho("Adding ", "0;32");
			$this->consoleEcho($file . "\r\n");	
			$content .= file_get_contents($this->_assetsPath . '/' . $file);
		}
		return $content;
	}

	private function minifyCSS($contents){
		if(!strlen($contents)) return "";
		require_once dirname(__FILE__) . "/MantisManager/minify/min/lib/Minify/CSS/Compressor.php";
		return Minify_CSS_Compressor::process($contents);
	}

	private function minifyJS($contents){
		if(!strlen($contents)) return "";
		require_once(dirname(__FILE__) . '/MantisManager/JShrink/src/JShrink/Minifier.php');
		return JShrink\Minifier::minify($contents);
		$contents = Minify_JS_ClosureCompiler::minify($contents);

		return $contents;
	}

	private function deleteDirectory($dir) {
		if (!file_exists($dir)) return true;
		if (!is_dir($dir)) return unlink($dir);
		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') continue;
			if (!$this->deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) return false;
		}
		return rmdir($dir);
	}

	private function extend($defaults = array(), $input = array()){
		if(isset($input)){
			foreach($defaults as $key=>$value){
				if(!array_key_exists($key, $input)){
					$input[$key] = $value;
				}
			}
		}else{
			$input = $defaults;
		}
		return $input;
	}
}

