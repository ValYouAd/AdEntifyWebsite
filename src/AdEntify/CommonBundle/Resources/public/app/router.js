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
   "modules/externalServicePhotos",
   "modules/photo"
],

function(app, fbLib, Facebook, HomePage, Photos, MyPhotos, Upload, FacebookAlbums, FacebookPhotos, InstagramPhotos,
         AdEntifyOAuth, FlickrSets, FlickrPhotos, ExternalServicePhotos, Photo) {

   var Router = Backbone.Router.extend({
      initialize: function() {
         this.listenTo(this, {
            'route': this.routeTriggered
         });

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
            tickerPhotos: new Photos.Collection(),
            myPhotos: new MyPhotos.Collection(),
            myTickerPhotos: new MyPhotos.Collection(),
            fbAlbums: new FacebookAlbums.Collection(),
            fbPhotos: new FacebookPhotos.Collection(),
            istgPhotos : new InstagramPhotos.Collection(),
            flrSets: new FlickrSets.Collection(),
            flrPhotos: new FlickrPhotos.Collection()
         };
         _.extend(this, collections);

         app.on('domchange:title', this.onDomChangeTitle, this);
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
         "flickr/sets/:id/photos/": "flickrPhotos",
         "photo/:id/": "photoDetail"
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
            "#content": new MyPhotos.Views.Content({
               photos: this.myPhotos
            }),
            "#menu-right": new MyPhotos.Views.Ticker({
               tickerPhotos: this.myTickerPhotos,
               tagged: true
            })
         });

         var that = this;
         app.oauth.loadAccessToken(function() {
            that.myPhotos.fetch({
               headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
               url: Routing.generate('api_v1_get_photo_user_photos', { tagged: true })
            });
            that.myTickerPhotos.fetch({
               headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
               url: Routing.generate('api_v1_get_photo_user_photos', { tagged: false })
            });
         });
      },

      meUntagged: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new MyPhotos.Views.Content({
               photos: this.myPhotos
            }),
            "#menu-right": new MyPhotos.Views.Ticker({
               tickerPhotos: this.myTickerPhotos,
               tagged: false
            })
         });

         var that = this;
         app.oauth.loadAccessToken(function() {
            that.myPhotos.fetch({
               headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
               url: Routing.generate('api_v1_get_photo_user_photos', { tagged: false })
            });
            that.myTickerPhotos.fetch({
               headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
               url: Routing.generate('api_v1_get_photo_user_photos', { tagged: true })
            });
         });
      },

      upload: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new Upload.Views.Content(),
            "#menu-right": new ExternalServicePhotos.Views.MenuRight()
         }).render();
      },

      facebookAlbums: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new FacebookAlbums.Views.List({
               albums: this.fbAlbums
            }),
            "#menu-right": new ExternalServicePhotos.Views.MenuRight()
         }).render();
      },

      facebookAlbumsPhotos: function(id) {
         this.reset();

         app.useLayout().setViews({
            "#content": new FacebookPhotos.Views.List({
               albumId: id,
               photos: this.fbPhotos
            }),
            "#menu-right": new ExternalServicePhotos.Views.MenuRight()
         }).render();
      },

      instagramPhotos: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new InstagramPhotos.Views.List({
               photos: this.istgPhotos
            }),
            "#menu-right": new ExternalServicePhotos.Views.MenuRight()
         }).render();
      },

      flickrSets: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new FlickrSets.Views.List({
               sets: this.flrSets
            }),
            "#menu-right": new ExternalServicePhotos.Views.MenuRight()
         }).render();
      },

      flickrPhotos: function(id) {
         this.reset();

         app.useLayout().setViews({
            "#content": new FlickrPhotos.Views.List({
               photos: this.flrPhotos,
               albumId: id
            }),
            "#menu-right": new ExternalServicePhotos.Views.MenuRight()
         }).render();
      },

      photoDetail: function(id) {
         this.reset();

         app.useLayout().setViews({
            "#content": new Photo.Views.Content({
               photo: new Photo.Model({ 'id': id })
            }),
            "#menu-right": new MyPhotos.Views.Ticker({
               tickerPhotos: this.myTickerPhotos,
               tagged: false
            })
         });

         var that = this;
         app.oauth.loadAccessToken(function() {
            that.myTickerPhotos.fetch({
               headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
               url: Routing.generate('api_v1_get_photo_user_photos', { tagged: true })
            });
         });
      },

      reset: function() {
         if (this.photos.length) {
            this.photos.reset();
         }
         if (this.tickerPhotos.length) {
            this.tickerPhotos.reset();
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
         if (this.myPhotos.length) {
            this.myPhotos.reset();
         }
         if (this.myTickerPhotos.length) {
            this.myTickerPhotos.reset();
         }
      },

      onDomChangeTitle: function(title) {
         if (typeof title !== 'undefined' && title != '') {
            $(document).attr('title', title);
         }
      },

      routeTriggered: function() {
         app.stopLoading();
      }
   });

   return Router;
});
