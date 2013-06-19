Mantis Asset Manager
====================

Warning: This will likely take a bit of tweaking to get plugged into your system, but it is totally worth it when you do.

### History
The Mantis Asset Manager is a system for the PHP framework Yii that minifies, combines, and publishes files. This is *not* a drop-in replacement for the standard CAssetManager, but rather a new system for managing your assets. This was made specifically for [getmantis.com](http://www.getmantis.com), which is hosted on Heroku. The problem with hosting Yii on Heroku (or on Amazon EC2) is that there is no persistent filesystem. (You can read about my first attempt to solve this problem [here](http://aaronfrancis.com/blog/2013/4/9/some-thoughts-about-hosting-yii-on-heroku).) Because there is no persistent filesystem, we can't rely on Yii's publishing mechanism because they could disappear at anytime. So this moves us to publishing our assets to Amazon S3, which is fine, because that's where they should be anyway. Uploading all your assets to S3 will likely take a long time, so we'd rather do that locally than rely on the webserver to do it. That way when we push our latest code live, all the assets are already published and ready to go.

### What The Mantis Manager Does
The Mantis Manager does several things. On first run, it will loop through a directory of your choice (defaults to ```protected/assets/```) and publish all your assets either locally or to your Amazon S3 bucket, depending on how how you run the command. In addition to publishing, it can minify and combine CSS and/or JS files prior to publishing.

When you run it the first time, Mantis will calculate the SHA1 of each file and store it away in the ```runtime``` folder. When you change a file, let's say edit a CSS file, you can run Mantis again and it will loop through and compare the SHAs and publish _only_ the changed files. This ensures that your end users don't have to get new versions of your assets when nothing has changed, their browsers can rely on cached versions. For the assets that _have_ changed, they will be at a new URL which will bust the cache immediately, especially helpful if you're using a CDN like Cloudfront.

### Configuration

#### Console
You'll need more configuration for your console than you will for your web app, because you'll be doing all the publishing from the console. Here is my console configuration:
```
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
```
You can see here that I've set up the manager to ignore any .psd files, minify all CSS, combine a few css files and put them into a ```css/combined.css``` file, minify all JS, combine a few JS into ```js/combined.js```. You can check the ```/config/env/local/console.php``` file for all the relevant info. Remember, this is just the config for the console application.

#### Web
For the web app, your configuration is much less, because you're just reading information, not doing any publishing.
```
'mantisManager'=>array(
	'class'=>'MantisManager',
	'type'=>'local'
),
```
We just set up the class and tell the manager what mode we're in (local or remote).
