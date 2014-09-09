1) Générer un client oAuth

php app/console adentify:oauth:client:create --name "AdEntify" --redirect-uri "https://www.adentify.com/" --grant-type token --grant-type authorization_code --grant-type password --grant-type "http://grants.api.adentify.com/facebook_access_token" --grant-type "http://grants.api.adentify.com/twitter_access_token"
php app/console adentify:oauth:client:create --name "Plugin Wordpress DEV" --redirect-uri "http://localhost/" --grant-type token --grant-type authorization_code

2) Cron

- MAJ des compteurs, une fois par jour
0 2 * * * php /var/www/adentify.com/htdocs/app/console adentify:update-counters --env=prod > /var/www/adentify.com/htdocs/app/logs/cron-updatecounters.log

- Récompenses - Toutes les heures
0 * * * * php /var/www/adentify.com/htdocs/app/console adentify:reward --env=prod > /var/www/adentify.com/htdocs/app/logs/cron-reward.log

- Tasks
@reboot /var/www/adentify.com/dev/upload-loop.sh


3) Triggers

Voir triggers.sql

4) Page contact


5) Deployment queries

Lancer deployment-queries.sql

6) Launch upload loop in background

7) CHMOD 777 sessions

chmod 777 app/var/sessions/

--------------------------------------------------

Prerender

getUrl function :

// Gets the URL to prerender from a request, stripping out unnecessary parts
function getUrl(req) {
    var decodedUrl
      , parts;

    try {
        decodedUrl = decodeURIComponent(req.url);
    } catch (e) {
        decodedUrl = req.url;
    }

    parts = url.parse(decodedUrl, true);

    // Remove the _escaped_fragment_ query parameter
    if (parts.query.hasOwnProperty('_escaped_fragment_')) {
        if(parts.query['_escaped_fragment_']) parts.pathname += parts.query['_escaped_fragment_'];
        delete parts.query['_escaped_fragment_'];
        delete parts.search;
    }

    var newUrl = url.format(parts);
    if(newUrl[0] == '/') newUrl = newUrl.substr(1);
    return newUrl;
}
