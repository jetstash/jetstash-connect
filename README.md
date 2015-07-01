# jetstash connect

![travis-ci](https://api.travis-ci.org/jetstash/jetstash-connect.svg)  

## installation

Install as a WordPress plugin by downloading the latest [release](https://github.com/jetstash/jetstash-connect/releases).

## usage

Currently only supports shortcodes for calling a single forms structure.

From the wp-admin panel:

```
[jetstash form="YOUR_FORM_ID"]
```

In a template/theme:

```
<?php do_shortcode('[jetstash form="YOUR_FORM_ID"]'); ?>
```

After a successful ajax submission you can listen for a custom event `jetstash` to hook in custom code. The state attribute contains the submission data and the response data from the application.

```
$(window).on("jetstash", function(e) {
  console.log(e.state);
});
```

## issues

Bugs, pull requests, features, etc. are all handled and maintained on GitHub.

[Open Issues](https://github.com/jetstash/jetstash-connect/issues)

## testing

The ability to run unit tests if updating the plugin has some requirements and caveats. To run the test successfully we need to shoe horn it into a working version of WordPress.

### requirements

 - PHP 5.4+  
 - WordPress 4.0+  
 - PHPUnit  
 - Clean WordPress install (with fresh database)  
 - WP-CLI  
 - Node.js  

### build

Currently the build system only concats && minifies the javascript into an admin and app version.

```
npm install
gulp
```

### running tests

You'll need to create an `env_local` file that contains the config information needed for the plugin to connect to the application. See the `env_sample` file for the structure. To run the tests:

```
./test.sh
```

### caveats

See the [wp-cli plugin unit tests wiki](https://github.com/wp-cli/wp-cli/wiki/Plugin-Unit-Tests) for more information about how WordPress is bootstrapped into phpunit.

## support

This plugin is a complimentary product offered to simplfy getting up and running with Jetstash + WordPress. If you need support it can be done directly through support channels on [Jetstash](https://www.jetstash.com).

## license

GPLv3
