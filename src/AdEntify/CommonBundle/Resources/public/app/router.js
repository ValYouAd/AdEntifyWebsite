define([
   // Application.
   "app",

   // FB SDK
   "facebook",
   "modules/facebook",

   // Modules
   "modules/homepage",
   "modules/photos",
   "modules/myPhotos",
   "modules/upload",
   "modules/facebookAlbums",
   "modules/facebookPhotos",
   "modules/instagramPhotos",
   "modules/adentifyOAuth",
   "modules/flickrSets",
   "modules/flickrPhotos",
   "modules/externalServicePhotos"
],

function(app, fbLib, Facebook, HomePage, Photos, MyPhotos, Upload, FacebookAlbums, FacebookPhotos, InstagramPhotos,
         AdEntifyOAuth, FlickrSets, FlickrPhotos, ExternalServicePhotos) {

   var Router = Backbone.Router.extend({
      initialize: function() {
         // Initialize Fb
         app.fb = new Facebook.Model();
         // Get AdEntify accesstoken for AdEntify API
         app.oauth = new AdEntifyOAuth.Model();
         app.oauth.loadAccessToken();

         // Facebook init
         FB.init({
            appId      : '159587157398776',                                   // App ID from the app dashboard
            channelUrl : channelUrl,  // Channel file for x-domain comms
            status     : false,                                                // Check Facebook Login status
            xfbml      : true,                                               // Look for social plugins on the page
            cookie     : true,
            oauth      : true
         });

         FB.getLoginStatus(function(response) {
            if (response.status === 'connected') {
               app.fb.connected(response);
            } else if (response.status === 'not_authorized') {
               app.fb.notLoggedIn();
            } else {
               app.fb.notLoggedIn();
            }
         });

         // Collections init
         var collections = {
            photos: new Photos.Collection(),
            myPhotos: new MyPhotos.Collection(),
            fbAlbums: new FacebookAlbums.Collection(),
            fbPhotos: new FacebookPhotos.Collection(),
            istgPhotos : new InstagramPhotos.Collection(),
            flrSets: new FlickrSets.Collection(),
            flrPhotos: new FlickrPhotos.Collection()
         };
         _.extend(this, collections);
      },

      routes: {
         "": "homepage",
         "untagged/": "untagged",
         "upload/": "upload",
         "me/tagged/": "meTagged",
         "me/untagged/": "meUntagged",
         "facebook/albums/": "facebookAlbums",
         "facebook/albums/:id/photos/": "facebookAlbumsPhotos",
         "instagram/photos/": "instagramPhotos",
         "flickr/sets/": "flickrSets",
         "flickr/sets/:id/photos/": "flickrPhotos"
      },

      homepage: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new Photos.Views.Content({
               tagged: true,
               photos: this.photos
            }),
            "#menu-right": new Photos.Views.Ticker(),
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
            "#menu-right": new Photos.Views.Ticker(),
            "#menu-tools": new Photos.Views.MenuTools()
         }).render();

         this.photos.fetch();
      },

      meTagged: function() {
         this.reset();

         app.useLayout().setViews({

         });

         this.myPhotos.fetch();
      },

      meUntagged: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new MyPhotos.Views.Content({
               photos: this.myPhotos
            }),
            "#menu-right": new MyPhotos.Views.Ticker(),
            "#menu-tools": new MyPhotos.Views.MenuTools()
         });

         var that = this;
         app.oauth.loadAccessToken(function() {
            that.myPhotos.fetch({
               headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
               url: Routing.generate('api_v1_get_photo_user_photos', { tagged: false })
            });
         });
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

      instagramPhotos: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new InstagramPhotos.Views.List({
               photos: this.istgPhotos
            })
         }).render();
      },

      flickrSets: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new FlickrSets.Views.List({
               sets: this.flrSets
            })
         }).render();
      },

      flickrPhotos: function(id) {
         this.reset();

         app.useLayout().setViews({
            "#content": new FlickrPhotos.Views.List({
               photos: this.flrPhotos,
               albumId: id
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
         if (this.istgPhotos.length) {
            this.istgPhotos.reset();
         }
         if (this.flrSets.length) {
            this.flrSets.reset();
         }
         if (this.flrPhotos.length) {
            this.flrPhotos.reset();
         }
      }
   });

   return Router;
});
