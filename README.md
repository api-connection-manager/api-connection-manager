##Wordpress API Connection Manager

[![Build Status](http://david-coombes.com:8080/jenkins/buildStatus/icon?job=API-Connection-Manager-v1.0)](http://david-coombes.com:8080/jenkins/job/API-Connection-Manager-v1.0/)

The API Connection Manager provides an API for developers to easily access 
third party resources, such as Facebook, Google, Twitter etc.

All third party keys and credentials are managed from the one settings page, so
there is no more need to have a settings page for each individual plugin.

Service can be activated, deactivated and installed/removed.


####Installation
Navigate to your wp-plugins folder run
```
git clone git@github.com:daithi-coombes/api-connection-manager.git
```
In the wordpress dashboard, navigate to plugins and activate the API
Connection Manager.


####Installing Modules
Each service has its own module file. To install the default module files,
navigate to your wp-plugins folder and run:
```
git clone git@github.com:daithi-coombes/api-con-mngr-modules.git
```
In the wordpress dashboard, navigate to plugins and activate the API Con Modules
plugin.

Once this is activated then you can see the services in Dashboard -> API Manager
-> Services Each of these services also have their own credentials page. To
add credentials for a service navigate to Dashboard -> API Manager -> Options


####Current Plugin List:
These are the current plugins that use API Connection Manager:
 - [Autoflow](https://github.com/daithi-coombes/autoflow) Easily setup user
 logins with a service
 - [Post File Importer](https://github.com/daithi-coombes/post-file-importer) Insert 
 files from services into posts


####Usage:
Once you have the plugin and some modules installed and activated in wordpress
then you can access the api. For this example we will be using the Facebook
module

Make sure the class-api-connection-manager.php file is included at the top of 
your plugin and the facebook credentials are saved in Dashboard -> API Manager
-> Options

```php
require_once( WP_PLUGIN_DIR  . "/api-connection-manager/index.php");


//In this example we will request a profile from Facebook:
$facebook = API_Con_Manager::get_service('Facebook');
$profile = $facebook->request('/my_fb_username');
var_dump($profile)


//The same can also be done for other services:
$google = API_Con_Manager::get_service('Google');
$profile = $google->request('/userinfo?alt=json&access_token=youraccess_token')
var_dump($profile)


//If nobody is logged in to a service then API_Con_Module::request will return 
an error with a login link.
$profile = $google->request('/userinfo?alt=json&access_token=youraccess_token')
if(is_wp_error($profile))
	print $google->get_login_button( __FILE__, array('cb', 'method'));

//in the above we print a login link, the parameters for get_login_button are
the callback file and function/class-method that will be called when the login
is finished.
```