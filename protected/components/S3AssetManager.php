<?php
class S3AssetManager extends CAssetManager{

    public $bucket;
    public $path;
    public $host;
    public $s3Component = 's3';
    public $cacheComponent = 'iron';
    private $_baseUrl;
    private $_published;
    private $_globalVersion;

	public function init(){
		Yii::trace('Init the S3AssetManager');
        $this->_globalVersion = Yii::app()->params['assetsVersion'];

		parent::init();
	}

    public function getBaseUrl(){
        if ($this->_baseUrl === null){
            $this->_baseUrl = 'http://'.$this->host.'/';
        }
        return $this->_baseUrl;
    }

    private function getCache(){
        if (!Yii::app()->{$this->cacheComponent}){
            throw new CException('You need to configure a cache storage or set the variable cacheComponent');
        }
        return Yii::app()->{$this->cacheComponent};
    }

    private function getS3(){
        if (!Yii::app()->{$this->s3Component}){
            throw new CException('You need to configure the S3 component or set the variable s3Component properly');
        }
        return Yii::app()->{$this->s3Component};
    }

    private function getCacheKey($path, $version){
        return $this->hash($version . '.' . $path);  
    }

    public function publish($path, $hashByName=false, $level=-1, $forceCopy=false, $assetVersion=0){
        Yii::trace("Publishing $path");
        if(isset($this->_published[$path])){
            return $this->_published[$path];   
        }

        if(($src = realpath($path)) == false){
            throw new CException(Yii::t('yii', 'The asset "{asset}" to be published does not exist.', array('{asset}' => $path)));
        }

        if(!$assetVersion) $assetVersion = is_null($this->_globalVersion) ? 1 : $this->_globalVersion;

        Yii::trace("Asset Version: $assetVersion");
        
        if(is_file($src)){
    		$contentType = CFileHelper::getMimeTypeByExtension($src); 

            $filename = basename($src);
            $directory = $this->hash(pathinfo($src,PATHINFO_DIRNAME));
            $directory = $this->path . "/" . $assetVersion . "/" . $directory;
            $destFile = $directory . "/" . $filename;

            if (
                $forceCopy || 
                $this->getCache()->get($this->getCacheKey($path, $assetVersion)) === false
            ){
                if ($this->getS3()->putObjectFile($src, $this->bucket, $destFile, $acl = S3::ACL_PUBLIC_READ, array(), $contentType)){
                    $this->getCache()->set($this->getCacheKey($path, $assetVersion), true, 0);
                    Yii::trace('Sent to S3');
                }else{
                    throw new CException('Could not send asset to S3');
                }
            }else{
                Yii::trace("Returning $path from cache");
            }

            return $this->_published[$path] = $this->getBaseUrl() . $destFile;
        }

        if(is_dir($src)){

            $directory = $this->hash($src);
            $directory = $this->path . "/" . $assetVersion . "/" . $directory;

            if (
                $forceCopy || 
                $this->getCache()->get($this->getCacheKey($path, $assetVersion)) === false
            ){
                $files = CFileHelper::findFiles($src, array(
                    'exclude' => $this->excludeFiles,
                    'level' => $level,
                ));

                foreach ($files as $f){
                    $destFile = $directory . '/' . str_replace($src . '/', "", $f);

        			$contentType = CFileHelper::getMimeTypeByExtension($destFile); 

                    if (!$this->getS3()->putObjectFile($f, $this->bucket, $destFile, $acl = S3::ACL_PUBLIC_READ, array(), $contentType)){
                        throw new CException('Could not send assets do S3');
                    }else{
                        Yii::trace("Sent $destFile to S3.");
                    }
                }

                $this->getCache()->set($this->getCacheKey($path, $assetVersion), true, 0); 
            }else{
                Yii::trace("Returning $path from cache");
            }
            return $this->_published[$path] = $this->getBaseUrl() . $directory;
        }
    }
}
