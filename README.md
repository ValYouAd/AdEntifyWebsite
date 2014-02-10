1) Générer un client oAuth

php app/console adentify:oauth:client:create --name "AdEntify" --redirect-uri "https://www.adentify.com/" --grant-type token --grant-type authorization_code --grant-type password --grant-type "http://grants.api.adentify.com/facebook_access_token"
php app/console adentify:oauth:client:create --name "Plugin Wordpress DEV" --redirect-uri "http://localhost/" --grant-type token --grant-type authorization_code

2) Cron

- MAJ des compteurs, une fois par jour
0 2 * * * php /var/www/adentify.com/htdocs/app/console adentify:update-counters --env=prod > /var/www/adentify.com/htdocs/app/logs/cron-updatecounters.log

- Récompenses - Toutes les heures
0 * * * * php /var/www/adentify.com/htdocs/app/console adentify:reward --env=prod > /var/www/adentify.com/htdocs/app/logs/cron-reward.log

- Tasks
* * * * * php /var/www/adentify.com/htdocs/app/console adentify:task:check --env=prod > /var/www/adentify.com/htdocs/app/logs/cron-taskcheck.log

3) Triggers

Voir triggers.sql

4) Page contact
