#! /bin/bash

COMPOSER_ALLOW_SUPERUSER=1 /usr/local/bin/composer -d /opt/Service update
php -f Service.php