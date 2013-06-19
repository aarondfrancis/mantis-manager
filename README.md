Mantis Asset Manager
==============

## History
The Mantis Asset Manager is a system for the PHP framework Yii that minifies, combines, and publishes files. This is *not* a drop-in replacement for the standard CAssetManager, but rather a new system for managing your assets. This was made specifically for getmantis.com, which is hosted on Heroku. The problem with hosting Yii on Heroku (or on Amazon EC2) is that there is no persistent filesystem. (You can read about my first attempt to solve this problem [here](http://aaronfrancis.com/blog/2013/4/9/some-thoughts-about-hosting-yii-on-heroku).) Because there is no persistent filesystem, we can't rely on Yii's publishing mechanism because they could disappear at anytime. So this moves us to publishing our assets to Amazon S3, which is fine, because that's where they should be anyway. Uploading all your assets to S3 will likely take a long time, so we'd rather do that locally than rely on the webserver to do it. That way when we push our latest code live, all the assets are already published and ready to go.

## What The Mantis Manager Does