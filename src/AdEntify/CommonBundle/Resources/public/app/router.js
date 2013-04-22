define([
   // Application.
   "app",

   // FB SDK
   "facebook",
   "modules/facebook",

   // Modules
   "modules/homepage",
   "modules/photos",
   "modules/upload",
   "modules/facebookAlbums",
   "modules/facebookPhotos"
],

function(app, fbLib, Facebook, HomePage, Photos, Upload, FacebookAlbums, FacebookPhotos) {

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
            photos: new Photos.Collection(),
            fbAlbums: new FacebookAlbums.Collection(),
            fbPhotos: new FacebookPhotos.Collection()
         };
         _.extend(this, collections);
      },

      statusChange: function(response) {
         // Init FB model with the facebook response
         app.fb.setFacebookResponse(response);
         if (app.fb.isConnected()) {
            this.$("#user-information").html('<img src="https://graph.facebook.com/' + app.fb.get('userId') + '/picture?width=20&height=20" />');
         }
         /*else {
            window.location.href = Routing.generate('fos_user_security_logout');
         }*/
      },

      routes: {
         "": "homepage",
         "untagged/": "untagged",
         "upload/": "upload",
         "facebook/albums/": "facebookAlbums",
         "facebook/albums/:id/photos/": "facebookAlbumsPhotos"
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

      facebookAlbums: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new FacebookAlbums.Views.List({
               albums: this.fbAlbums
            })
         }).render();
      },

      facebookAlbumsPhotos: function(id) {
         this.reset();

         app.useLayout().setViews({
            "#content": new FacebookPhotos.Views.List({
               albumId: id,
               photos: this.fbPhotos
            })
         }).render();
      },

      reset: function() {
         if (this.photos.length) {
            this.photos.reset();
         }
         if (this.fbAlbums.length) {
            this.fbAlbums.reset();
         }
         if (this.fbPhotos.length) {
            this.fbPhotos.reset();
         }
      }
   });

   return Router;
});
