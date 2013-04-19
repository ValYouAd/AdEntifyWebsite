define([
   // Application.
   "app",

   // FB SDK
   "facebook",
   "modules/facebook",

   // Modules
   "modules/homepage",
   "modules/photos",
   "modules/upload"
],

function(app, fbLib, Facebook, HomePage, Photos, Upload) {

   var Router = Backbone.Router.extend({
      initialize: function() {
         // Facebook init
         FB.init({
            appId      : '159587157398776',                                   // App ID from the app dashboard
            channelUrl : '//localhost/AdEntifyFacebookApp/web/channel.html', // Channel file for x-domain comms
            status     : true,                                                // Check Facebook Login status
            xfbml      : true                                                 // Look for social plugins on the page
         });

         FB.Event.subscribe('auth.statusChange', this.statusChange);

         app.fb = new Facebook.Model();

         // Collections init
         var collections = {
            photos: new Photos.Collection()
         };
         _.extend(this, collections);
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
         "untagged/": "untagged",
         "upload/": "upload"
      },

      homepage: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new Photos.Views.Content({
               tagged: true,
               photos: this.photos
            }),
            "#ticker": new Photos.Views.Ticker(),
            "#menu-tools": new Photos.Views.MenuTools()
         }).render();

         this.photos.fetch();
      },

      untagged: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new Photos.Views.Content({
               tagged: false,
               photos: this.photos
            }),
            "#ticker": new Photos.Views.Ticker(),
            "#menu-tools": new Photos.Views.MenuTools()
         }).render();

         this.photos.fetch();
      },

      upload: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new Upload.Views.Content()
         }).render();
      },

      reset: function() {
         if (this.photos.length) {
            this.photos.reset();
         }
      }
   });

   return Router;
});
