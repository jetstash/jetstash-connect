# jetstash connect

## installation

Install as a WordPress plugin by [downloading](https://github.com/jetstash/jetstash-connect/archive/master.zip) or cloning the project as a submodule.

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

## issues

Bugs, pull requests, features, etc. are all handled and maintained on GitHub.

[Open Issues](https://github.com/jetstash/jetstash-connect/issues)

## testing && contributing

The ability to run unit tests if updating the plugin has some requirements and caveats. To run the test successfully we need to shoe horn it into

### requirements

 - PHP 5.4+
 - PHPUnit
 - Clean WordPress install (with fresh database)

### install && run

You'll need to create an `env_local` file that contains the config information needed for the plugin to connect to the application.

```
phpunit tests/testJetstashConnect.php
```

### caveats

See the [wp-cli plugin unit tests wiki](https://github.com/wp-cli/wp-cli/wiki/Plugin-Unit-Tests) for more information about how WordPress is bootstrapped into phpunit.

On restart the /tmp/ directory is removed forcing you to rerun these commands, you'll need to make sure the database wordpress_dev does not exist.

```
cd $(wp plugin path --dir jetstash-connect)
echo 'drop database wordpress_dev;' | mysql -uroot
bash bin/install-wp-tests.sh wordpress_dev root '' localhost latest
```

## support

This plugin is a complimentary product offered to simplfy getting up and running with Jetstash + WordPress. If you need support it can be done directly through support channels on [Jetstash](https://www.jetstash.com).

## license

MIT