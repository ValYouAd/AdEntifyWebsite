define([
   // Application.
   "app",

   // FB SDK
   "facebook",

   // Modules
   "modules/homepage",
   "modules/pics",
   "modules/facebook"
],

function(app, fbLib, HomePage, Pics, Facebook) {

   var Router = Backbone.Router.extend({
      initialize: function() {
         FB.init({
            appId      : '159587157398776',                                   // App ID from the app dashboard
            channelUrl : '//localhost/AdEntifyFacebookApp/web/channel.html', // Channel file for x-domain comms
            status     : true,                                                // Check Facebook Login status
            xfbml      : true                                                 // Look for social plugins on the page
         });

         FB.Event.subscribe('auth.statusChange', this.statusChange);

         app.fb = new Facebook.Model();
      },

      statusChange: function(response) {
         // Init FB model with the facebook response
         app.fb.setFacebookResponse(response);

         if (app.fb.isConnected()) {
            this.$("#fb-connect-status").html(app.fb.get('status'));
            FB.api('/me', function(response) {
               this.$("#user-information").html('<span class="label label-success">Bienvenue ' + response.name + '</span>');
            });
         }
         /*else {
            window.location.href = Routing.generate('fos_user_security_logout');
         }*/
      },

      routes: {
         "": "homepage",
         "pics/": "pics"
      },

      homepage: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new HomePage.Views.Content()
         }).render();
      },

      pics: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new Pics.Views.Content()
         }).render();
      },

      reset: function() { }
   });

   return Router;
});
