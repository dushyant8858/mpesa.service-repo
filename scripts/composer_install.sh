#!/bin/bash
cd /var/www/html
pwd 
ls -al
composer install -n
composer dump-autoload -o 
