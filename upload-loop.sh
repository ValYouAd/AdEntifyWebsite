#!/bin/sh
while true
do
php /var/www/adentify.com/dev/app/console adentify:task:check --env=prod > /var/www/adentify.com/dev/app/logs/cron-taskcheck.log 2>&1
sleep 5
done