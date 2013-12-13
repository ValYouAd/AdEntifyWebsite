1) Générer un client oAuth

php app/console adentify:oauth:client:create --name "AdEntify" --redirect-uri "http://localhost/AdEntifyFacebookApp/web/" --grant-type token --grant-type authorization_code --grant-type password --grant-type "http://grants.api.adentify.com/facebook_access_token"
php app/console adentify:oauth:client:create --name "Plugin Wordpress DEV" --redirect-uri "http://localhost/" --grant-type token --grant-type authorization_code

2) Cron

- MAJ des compteurs, une fois par jour
php app/console adentify:update-counters

3) Triggers

Voir triggers.sql