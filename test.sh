#! /bin/bash

echo 'DROP DATABASE IF EXISTS wordpress_test;' | mysql -uroot
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
phpunit tests/testJetstashConnect.php
